<?php

namespace App\Models;

use Database\Factories\WorkoutPlanDayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['workout_plan_id', 'name', 'order'])]
class WorkoutPlanDay extends Model
{
    /** @use HasFactory<WorkoutPlanDayFactory> */
    use HasFactory;

    public function workoutPlan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutPlanDayExercise::class)->orderBy('position');
    }
}
