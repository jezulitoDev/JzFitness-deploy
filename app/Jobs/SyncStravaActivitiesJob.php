<?php

namespace App\Jobs;

use App\Exceptions\StravaAuthorizationException;
use App\Models\StravaAccount;
use App\Services\StravaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncStravaActivitiesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?int $userId = null) {}

    public function handle(StravaService $stravaService): void
    {
        $query = StravaAccount::query()->with('user');

        if ($this->userId !== null) {
            $query->where('user_id', $this->userId);
        }

        $query->each(function (StravaAccount $account) use ($stravaService): void {
            if ($account->user === null) {
                return;
            }

            try {
                $stravaService->syncActivities($account->user);
            } catch (StravaAuthorizationException $exception) {
                $stravaService->markAccountDisconnected(
                    $account->user,
                    $exception->getMessage(),
                );

                Log::warning('Strava sync skipped: authorization failed', [
                    'user_id' => $account->user_id,
                    'strava_id' => $account->strava_id,
                    'message' => $exception->getMessage(),
                ]);
            } catch (Throwable $exception) {
                Log::error('Strava sync failed', [
                    'user_id' => $account->user_id,
                    'strava_id' => $account->strava_id,
                    'message' => $exception->getMessage(),
                ]);
            }
        });
    }
}
