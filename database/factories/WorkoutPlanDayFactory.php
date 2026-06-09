<?php

namespace Database\Factories;

use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutPlanDay>
 */
class WorkoutPlanDayFactory extends Factory
{
    protected $model = WorkoutPlanDay::class;

    public function definition(): array
    {
        return [
            'workout_plan_id' => WorkoutPlan::factory(),
            'name' => fake()->word(),
            'order' => fake()->numberBetween(0, 5),
        ];
    }
}
