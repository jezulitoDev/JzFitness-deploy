<?php

namespace App\Services;

use App\Exceptions\StravaAuthorizationException;
use App\Models\StravaAccount;
use App\Models\StravaActivity;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class StravaService
{
    private const TOKEN_URL = 'https://www.strava.com/oauth/token';

    private const ACTIVITIES_URL = 'https://www.strava.com/api/v3/athlete/activities';

    public function isConfigured(): bool
    {
        $clientId = config('services.strava.client_id');
        $clientSecret = config('services.strava.client_secret');

        return filled($clientId) && filled($clientSecret);
    }

    public function connectAccount(User $user, SocialiteUser $socialiteUser): StravaAccount
    {
        $refreshToken = $socialiteUser->refreshToken ?? '';

        if ($refreshToken === '') {
            Log::warning('Strava OAuth missing refresh_token', ['user_id' => $user->id]);

            throw new StravaAuthorizationException(
                __('Strava did not return a refresh token. Please try connecting again.'),
                $user->id,
            );
        }

        $account = StravaAccount::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'strava_id' => (int) $socialiteUser->getId(),
                'access_token' => $socialiteUser->token,
                'refresh_token' => $refreshToken,
                'expires_at' => $this->resolveExpiresAt([
                    'expires_in' => $socialiteUser->expiresIn ?? 21600,
                ]),
            ],
        );

        return $account;
    }

    public function disconnectAccount(User $user): void
    {
        $user->stravaAccount()?->delete();
        $user->stravaActivities()->delete();
    }

    public function markAccountDisconnected(User $user, ?string $reason = null): void
    {
        if ($reason !== null) {
            Log::warning('Strava account disconnected due to auth failure', [
                'user_id' => $user->id,
                'reason' => $reason,
            ]);
        }

        $this->disconnectAccount($user);
    }

    public function ensureValidAccessToken(StravaAccount $account, bool $force = false): StravaAccount
    {
        $account = $account->fresh();

        if ($account === null) {
            throw new StravaAuthorizationException(userId: null);
        }

        if (! $force && ! $account->needsRefresh()) {
            return $account;
        }

        $lock = Cache::lock("strava:refresh:{$account->id}", 30);

        try {
            $lock->block(10);

            $account = $account->fresh();

            if ($account === null) {
                throw new StravaAuthorizationException(userId: null);
            }

            if (! $force && ! $account->needsRefresh()) {
                return $account;
            }

            return $this->performTokenRefresh($account);
        } finally {
            $lock->release();
        }
    }

    public function refreshAllTokens(): void
    {
        StravaAccount::query()
            ->with('user')
            ->each(function (StravaAccount $account): void {
                if ($account->user === null) {
                    return;
                }

                try {
                    $this->ensureValidAccessToken($account);
                } catch (StravaAuthorizationException $exception) {
                    $this->markAccountDisconnected(
                        $account->user,
                        $exception->getMessage(),
                    );
                }
            });
    }

    /**
     * @return array{synced: int}
     */
    public function syncActivities(User $user): array
    {
        $account = $user->stravaAccount;

        if ($account === null) {
            return ['synced' => 0];
        }

        $account = $this->ensureValidAccessToken($account);

        $after = $user->stravaActivities()
            ->max('started_at');

        $afterTimestamp = $after
            ? Carbon::parse($after)->subDay()->timestamp
            : null;

        $synced = 0;
        $page = 1;

        do {
            $query = [
                'page' => $page,
                'per_page' => 50,
            ];

            if ($afterTimestamp !== null) {
                $query['after'] = $afterTimestamp;
            }

            $response = $this->request($account, 'get', self::ACTIVITIES_URL, $query);
            $account = $account->fresh();

            $activities = $response->json();

            if (! is_array($activities) || count($activities) === 0) {
                break;
            }

            foreach ($activities as $activity) {
                $this->upsertActivity($user, $activity);
                $synced++;
            }

            $page++;
        } while (count($activities) === 50 && $page <= 10);

        return ['synced' => $synced];
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function request(
        StravaAccount $account,
        string $method,
        string $url,
        array $query = [],
        bool $isRetry = false,
    ): Response {
        $account = $this->ensureValidAccessToken($account);

        $response = Http::withToken($account->access_token)
            ->{$method}($url, $query);

        if ($response->status() === 401 && ! $isRetry) {
            $account = $this->ensureValidAccessToken($account->fresh(), force: true);

            return $this->request($account, $method, $url, $query, isRetry: true);
        }

        if ($response->status() === 401) {
            $userId = $account->user_id;

            throw new StravaAuthorizationException(
                __('Strava access was revoked. Please reconnect your account.'),
                $userId,
            );
        }

        if (! $response->successful()) {
            $response->throw();
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    public function upsertActivity(User $user, array $activity): StravaActivity
    {
        return StravaActivity::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'strava_activity_id' => $activity['id'],
            ],
            [
                'name' => $activity['name'] ?? 'Activity',
                'sport_type' => $activity['sport_type'] ?? $activity['type'] ?? 'Other',
                'distance' => $activity['distance'] ?? 0,
                'moving_time' => $activity['moving_time'] ?? 0,
                'elapsed_time' => $activity['elapsed_time'] ?? 0,
                'elevation_gain' => $activity['total_elevation_gain'] ?? 0,
                'started_at' => Carbon::parse($activity['start_date'] ?? now()),
                'raw_json' => $activity,
            ],
        );
    }

    protected function performTokenRefresh(StravaAccount $account): StravaAccount
    {
        if ($account->refresh_token === '') {
            throw new StravaAuthorizationException(
                __('Missing Strava refresh token. Please reconnect your account.'),
                $account->user_id,
            );
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => config('services.strava.client_id'),
            'client_secret' => config('services.strava.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $account->refresh_token,
        ]);

        if (! $response->successful()) {
            Log::warning('Strava token refresh failed', [
                'user_id' => $account->user_id,
                'strava_id' => $account->strava_id,
                'status' => $response->status(),
            ]);

            throw new StravaAuthorizationException(
                __('Could not refresh Strava token. Please reconnect your account.'),
                $account->user_id,
            );
        }

        /** @var array<string, mixed> $data */
        $data = $response->json();

        $this->applyTokenResponse($account, $data);

        return $account->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function applyTokenResponse(StravaAccount $account, array $data): void
    {
        $account->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $account->refresh_token,
            'expires_at' => $this->resolveExpiresAt($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveExpiresAt(array $data): Carbon
    {
        if (isset($data['expires_at']) && is_numeric($data['expires_at'])) {
            return Carbon::createFromTimestamp((int) $data['expires_at']);
        }

        return Carbon::now()->addSeconds((int) ($data['expires_in'] ?? 21600));
    }
}
