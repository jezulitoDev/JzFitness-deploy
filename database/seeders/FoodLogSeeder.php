<?php

namespace Database\Seeders;

use App\Enums\MealType;
use App\Models\FoodLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class FoodLogSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if ($user === null) {
            return;
        }

        $meals = [
            MealType::Breakfast => [
                ['name' => 'Avena con plátano', 'quantity' => '80 g + 1 unidad', 'calories' => 390, 'protein_g' => 12.0, 'carbs_g' => 70.0, 'fat_g' => 6.0],
                ['name' => 'Café con leche', 'quantity' => '1 taza', 'calories' => 60, 'protein_g' => 3.0, 'carbs_g' => 5.0, 'fat_g' => 3.0],
            ],
            MealType::Lunch => [
                ['name' => 'Pollo a la plancha', 'quantity' => '200 g', 'calories' => 330, 'protein_g' => 62.0, 'carbs_g' => 0.0, 'fat_g' => 7.0],
                ['name' => 'Arroz integral', 'quantity' => '150 g', 'calories' => 165, 'protein_g' => 4.0, 'carbs_g' => 34.0, 'fat_g' => 1.5],
            ],
            MealType::Snack => [
                ['name' => 'Yogur griego con nueces', 'quantity' => '1 unidad + 20 g', 'calories' => 250, 'protein_g' => 12.0, 'carbs_g' => 10.0, 'fat_g' => 18.0],
            ],
            MealType::Dinner => [
                ['name' => 'Salmón al horno con verduras', 'quantity' => '180 g', 'calories' => 380, 'protein_g' => 38.0, 'carbs_g' => 12.0, 'fat_g' => 20.0],
            ],
        ];

        foreach ([now()->toDateString(), now()->subDay()->toDateString()] as $date) {
            foreach ($meals as $mealType => $items) {
                foreach ($items as $item) {
                    FoodLog::query()->firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'consumed_on' => $date,
                            'meal_type' => $mealType,
                            'name' => $item['name'],
                        ],
                        $item,
                    );
                }
            }
        }
    }
}
