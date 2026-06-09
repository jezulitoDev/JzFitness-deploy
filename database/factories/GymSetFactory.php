<?php

namespace Database\Factories;

use App\Models\GymSessionExercise;
use App\Models\GymSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymSet>
 */
class GymSetFactory extends Factory
{
    protected $model = GymSet::class;

    public function definition(): array
    {
        return [
            'gym_session_exercise_id' => GymSessionExercise::factory(),
            'weight' => fake()->randomFloat(1, 20, 120),
            'reps' => fake()->numberBetween(5, 12),
            'duration' => null,
            'rest_seconds' => 90,
            'rpe' => fake()->optional()->randomFloat(1, 6, 10),
            'completed' => fake()->boolean(80),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'completed' => true,
        ]);
    }
}
