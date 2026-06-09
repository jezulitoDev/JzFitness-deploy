<?php

namespace App\Models;

use Database\Factories\WorkoutPlanDayExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['workout_plan_day_id', 'exercise_id', 'position', 'default_rest_seconds', 'target_sets', 'target_reps', 'target_weight'])]
class WorkoutPlanDayExercise extends Model
{
    /** @use HasFactory<WorkoutPlanDayExerciseFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_sets' => 'integer',
            'target_reps' => 'integer',
            'target_weight' => 'float',
        ];
    }

    public function workoutPlanDay(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlanDay::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
