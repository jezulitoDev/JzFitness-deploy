<?php

namespace App\Models;

use Database\Factories\ScheduledWorkoutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'workout_plan_id', 'workout_plan_day_id', 'title', 'scheduled_date', 'completed_at', 'notes'])]
class ScheduledWorkout extends Model
{
    /** @use HasFactory<ScheduledWorkoutFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'completed_at' => 'datetime',
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

    public function workoutPlanDay(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlanDay::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function displayName(): string
    {
        if ($this->title !== null && $this->title !== '') {
            return $this->title;
        }

        $parts = array_filter([
            $this->workoutPlan?->name,
            $this->workoutPlanDay?->name,
        ]);

        return $parts === [] ? 'Entrenamiento' : implode(' · ', $parts);
    }
}
