<?php

namespace App\Http\Controllers;

use App\Enums\FoodCategory;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FoodController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::enum(FoodCategory::class)],
        ]);

        $search = trim($validated['q'] ?? '');
        $category = $validated['category'] ?? null;

        $foods = Food::query()
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($category !== null, fn ($q) => $q->where('category', $category))
            ->orderBy('name')
            ->limit(25)
            ->get()
            ->map(fn (Food $food): array => [
                'id' => $food->id,
                'name' => $food->name,
                'category' => $food->category->value,
                'category_label' => $food->category->label(),
                'calories_per_100g' => $food->calories_per_100g,
                'protein_per_100g' => $food->protein_per_100g,
                'carbs_per_100g' => $food->carbs_per_100g,
                'fat_per_100g' => $food->fat_per_100g,
                'serving_size_g' => $food->serving_size_g,
                'serving_label' => $food->serving_label,
            ]);

        return response()->json(['foods' => $foods]);
    }
}
