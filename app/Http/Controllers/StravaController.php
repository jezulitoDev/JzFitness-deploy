<?php

namespace App\Http\Controllers;

use App\Exceptions\StravaAuthorizationException;
use App\Jobs\SyncStravaActivitiesJob;
use App\Services\StravaService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class StravaController extends Controller
{
    public function __construct(protected StravaService $stravaService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $account = $user->stravaAccount;

        return Inertia::render('strava/index', [
            'stravaConfigured' => $this->stravaService->isConfigured(),
            'account' => $account,
            'tokenExpiresAt' => $account?->expires_at?->toIso8601String(),
            'tokenExpiresInMinutes' => $account?->minutesUntilExpiry(),
            'needsReconnect' => false,
            'activities' => $user->stravaActivities()
                ->latest('started_at')
                ->limit(20)
                ->get()
                ->map(fn ($activity) => [
                    ...$activity->toArray(),
                    'started_at_label' => $activity->started_at->format('d/m/Y'),
                ])
                ->values(),
        ]);
    }

    public function connect(): RedirectResponse
    {
        if (! $this->stravaService->isConfigured()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('Strava API credentials are not configured. Add STRAVA_CLIENT_ID and STRAVA_CLIENT_SECRET to your .env file.'),
            ]);

            return to_route('strava.index');
        }

        return $this->stravaDriver()
            ->scopes(['read', 'activity:read_all'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('Strava authorization was denied.'),
            ]);

            return to_route('strava.index');
        }

        try {
            $socialiteUser = $this->stravaDriver()->user();

            $this->stravaService->connectAccount($request->user(), $socialiteUser);

            $result = $this->stravaService->syncActivities($request->user());

            SyncStravaActivitiesJob::dispatch($request->user()->id);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => __('Strava account connected. :count activities synced.', ['count' => $result['synced']]),
            ]);
        } catch (InvalidStateException) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('OAuth session expired. Use :url for the whole flow (do not mix with 127.0.0.1).', [
                    'url' => config('app.url'),
                ]),
            ]);
        } catch (ClientException $exception) {
            $body = (string) $exception->getResponse()?->getBody();
            $isInvalidApplication = str_contains($body, '"resource":"Application"')
                && str_contains($body, '"code":"invalid"');

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $isInvalidApplication
                    ? __('Invalid Strava Client ID or Client Secret. Copy both from https://www.strava.com/settings/api (field "Client Secret", not the access token). Then run php artisan config:clear.')
                    : __('Strava authorization failed. Please try connecting again.'),
            ]);
        } catch (StravaAuthorizationException $exception) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Strava OAuth callback failed', [
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => config('app.debug')
                    ? $exception->getMessage()
                    : __('Could not connect Strava. Please try again.'),
            ]);
        }

        return to_route('strava.index');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $this->stravaService->disconnectAccount($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Strava account disconnected.')]);

        return to_route('strava.index');
    }

    public function sync(Request $request): RedirectResponse
    {
        try {
            $result = $this->stravaService->syncActivities($request->user());

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => __(':count activities synced.', ['count' => $result['synced']]),
            ]);
        } catch (StravaAuthorizationException $exception) {
            $this->stravaService->markAccountDisconnected($request->user(), $exception->getMessage());

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);
        }

        return to_route('strava.index');
    }

    /**
     * @return \Laravel\Socialite\Contracts\Provider|\Laravel\Socialite\Two\AbstractProvider
     */
    protected function stravaDriver()
    {
        return Socialite::driver('strava')
            ->redirectUrl(route('strava.callback', absolute: true));
    }
}
