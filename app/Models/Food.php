<?php

namespace App\Models;

use App\Enums\FoodCategory;
use Database\Factories\FoodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category', 'calories_per_100g', 'protein_per_100g', 'carbs_per_100g', 'fat_per_100g', 'serving_size_g', 'serving_label'])]
class Food extends Model
{
    /** @use HasFactory<FoodFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => FoodCategory::class,
            'calories_per_100g' => 'integer',
            'protein_per_100g' => 'float',
            'carbs_per_100g' => 'float',
            'fat_per_100g' => 'float',
            'serving_size_g' => 'float',
        ];
    }

    /**
     * Nutritional values for a given amount in grams, mirroring the diary fields.
     *
     * @return array{calories: int, protein_g: float, carbs_g: float, fat_g: float}
     */
    public function macrosForGrams(float $grams): array
    {
        $factor = $grams / 100;

        return [
            'calories' => (int) round($this->calories_per_100g * $factor),
            'protein_g' => round($this->protein_per_100g * $factor, 1),
            'carbs_g' => round($this->carbs_per_100g * $factor, 1),
            'fat_g' => round($this->fat_per_100g * $factor, 1),
        ];
    }
}
