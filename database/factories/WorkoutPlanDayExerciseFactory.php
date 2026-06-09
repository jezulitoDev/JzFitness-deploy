<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\WorkoutPlanDay;
use App\Models\WorkoutPlanDayExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutPlanDayExercise>
 */
class WorkoutPlanDayExerciseFactory extends Factory
{
    protected $model = WorkoutPlanDayExercise::class;

    public function definition(): array
    {
        return [
            'workout_plan_day_id' => WorkoutPlanDay::factory(),
            'exercise_id' => Exercise::factory(),
            'position' => fake()->numberBetween(0, 10),
            'default_rest_seconds' => 90,
        ];
    }
}
