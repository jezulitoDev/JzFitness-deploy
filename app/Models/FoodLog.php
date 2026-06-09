<?php

namespace App\Models;

use App\Enums\MealType;
use Database\Factories\FoodLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'consumed_on', 'meal_type', 'name', 'quantity', 'calories', 'protein_g', 'carbs_g', 'fat_g'])]
class FoodLog extends Model
{
    /** @use HasFactory<FoodLogFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consumed_on' => 'date',
            'meal_type' => MealType::class,
            'calories' => 'integer',
            'protein_g' => 'float',
            'carbs_g' => 'float',
            'fat_g' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
