<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'muscle_group_id' => MuscleGroup::factory(),
            'name' => fake()->words(2, true),
            'equipment' => fake()->randomElement(['Barbell', 'Dumbbell', 'Machine', 'Bodyweight', 'Cable']),
            'description' => fake()->optional()->sentence(),
            'video_url' => null,
        ];
    }
}
