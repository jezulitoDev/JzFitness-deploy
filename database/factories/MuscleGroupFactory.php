<?php

namespace Database\Factories;

use App\Models\MuscleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MuscleGroup>
 */
class MuscleGroupFactory extends Factory
{
    protected $model = MuscleGroup::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
