<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\GymSession;
use App\Models\GymSessionExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymSessionExercise>
 */
class GymSessionExerciseFactory extends Factory
{
    protected $model = GymSessionExercise::class;

    public function definition(): array
    {
        return [
            'gym_session_id' => GymSession::factory(),
            'exercise_id' => Exercise::factory(),
            'order' => fake()->numberBetween(0, 10),
            'default_rest_seconds' => 90,
        ];
    }
}
