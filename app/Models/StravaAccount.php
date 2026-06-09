<?php

namespace App\Models;

use Database\Factories\StravaAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['user_id', 'strava_id', 'access_token', 'refresh_token', 'expires_at'])]
#[Hidden(['access_token', 'refresh_token'])]
class StravaAccount extends Model
{
    public const REFRESH_BUFFER_SECONDS = 3600;

    /** @use HasFactory<StravaAccountFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'strava_id' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function needsRefresh(): bool
    {
        return $this->expires_at->lte(now()->addSeconds(self::REFRESH_BUFFER_SECONDS));
    }

    public function minutesUntilExpiry(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return (int) now()->diffInMinutes($this->expires_at);
    }
}
