<?php

namespace Database\Factories;

use App\Enums\FoodCategory;
use App\Models\Food;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Food>
 */
class FoodFactory extends Factory
{
    protected $model = Food::class;

    public function definition(): array
    {
        $protein = fake()->randomFloat(1, 0, 30);
        $carbs = fake()->randomFloat(1, 0, 60);
        $fat = fake()->randomFloat(1, 0, 30);

        return [
            'name' => fake()->unique()->words(2, true),
            'category' => fake()->randomElement(FoodCategory::cases()),
            'calories_per_100g' => (int) round($protein * 4 + $carbs * 4 + $fat * 9),
            'protein_per_100g' => $protein,
            'carbs_per_100g' => $carbs,
            'fat_per_100g' => $fat,
            'serving_size_g' => fake()->optional(0.4)->randomFloat(1, 10, 250),
            'serving_label' => null,
        ];
    }

    public function category(FoodCategory $category): static
    {
        return $this->state(fn (): array => [
            'category' => $category,
        ]);
    }
}
