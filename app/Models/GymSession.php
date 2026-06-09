<?php

namespace App\Models;

use Database\Factories\GymSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'workout_plan_id', 'started_at', 'ended_at', 'notes'])]
class GymSession extends Model
{
    /** @use HasFactory<GymSessionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutPlan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(GymSessionExercise::class)->orderBy('order');
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }
}
