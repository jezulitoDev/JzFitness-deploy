<?php

namespace Database\Factories;

use App\Enums\MealType;
use App\Models\FoodLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FoodLog>
 */
class FoodLogFactory extends Factory
{
    protected $model = FoodLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'consumed_on' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
            'meal_type' => fake()->randomElement(MealType::cases()),
            'name' => fake()->randomElement([
                'Pollo a la plancha', 'Arroz integral', 'Avena con plátano',
                'Yogur griego', 'Ensalada mixta', 'Salmón al horno', 'Tortilla francesa',
            ]),
            'quantity' => fake()->randomElement(['100 g', '150 g', '200 g', '1 unidad', '1 taza']),
            'calories' => fake()->numberBetween(50, 800),
            'protein_g' => fake()->optional(0.8)->randomFloat(1, 0, 60),
            'carbs_g' => fake()->optional(0.8)->randomFloat(1, 0, 100),
            'fat_g' => fake()->optional(0.8)->randomFloat(1, 0, 40),
        ];
    }

    public function on(string $date): static
    {
        return $this->state(fn (): array => [
            'consumed_on' => $date,
        ]);
    }

    public function meal(MealType $mealType): static
    {
        return $this->state(fn (): array => [
            'meal_type' => $mealType,
        ]);
    }
}
