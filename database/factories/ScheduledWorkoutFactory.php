<?php

namespace Database\Factories;

use App\Models\ScheduledWorkout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduledWorkout>
 */
class ScheduledWorkoutFactory extends Factory
{
    protected $model = ScheduledWorkout::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workout_plan_id' => null,
            'workout_plan_day_id' => null,
            'title' => fake()->randomElement(['Push', 'Pull', 'Pierna', 'Full body', 'Cardio']),
            'scheduled_date' => fake()->dateTimeBetween('-2 weeks', '+2 weeks')->format('Y-m-d'),
            'completed_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'completed_at' => now(),
        ]);
    }
}
