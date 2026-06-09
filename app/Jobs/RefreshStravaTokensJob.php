<?php

namespace App\Jobs;

use App\Services\StravaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshStravaTokensJob implements ShouldQueue
{
    use Queueable;

    public function handle(StravaService $stravaService): void
    {
        $stravaService->refreshAllTokens();
    }
}
