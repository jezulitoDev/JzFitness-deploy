<?php

namespace App\Http\Requests;

use App\Enums\MealType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFoodLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'consumed_on' => ['required', 'date'],
            'meal_type' => ['required', Rule::enum(MealType::class)],
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'string', 'max:100'],
            'calories' => ['required', 'integer', 'min:0', 'max:10000'],
            'protein_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'carbs_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'fat_g' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ];
    }
}
