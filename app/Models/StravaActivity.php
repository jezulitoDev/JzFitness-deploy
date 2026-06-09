<?php

namespace App\Models;

use App\Enums\SportType;
use Database\Factories\StravaActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'strava_activity_id',
    'name',
    'sport_type',
    'distance',
    'moving_time',
    'elapsed_time',
    'elevation_gain',
    'started_at',
    'raw_json',
])]
class StravaActivity extends Model
{
    /** @use HasFactory<StravaActivityFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'strava_activity_id' => 'integer',
            'distance' => 'decimal:2',
            'elevation_gain' => 'decimal:2',
            'started_at' => 'datetime',
            'raw_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sportTypeEnum(): SportType
    {
        return SportType::tryFromStrava($this->sport_type);
    }
}
