<?php

namespace Database\Factories;

use App\Models\GymSession;
use App\Models\User;
use App\Models\WorkoutPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymSession>
 */
class GymSessionFactory extends Factory
{
    protected $model = GymSession::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'user_id' => User::factory(),
            'workout_plan_id' => null,
            'started_at' => $startedAt,
            'ended_at' => fake()->optional(0.8)->dateTimeBetween($startedAt, 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'ended_at' => null,
        ]);
    }

    public function forPlan(): static
    {
        return $this->state(fn (): array => [
            'workout_plan_id' => WorkoutPlan::factory(),
        ]);
    }
}
