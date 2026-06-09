<?php

namespace App\Http\Requests\Settings;

use App\Enums\ExperienceLevel;
use App\Enums\FitnessGoal;
use App\Enums\WeightUnit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FitnessProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fitness_goal' => ['required', Rule::enum(FitnessGoal::class)],
            'experience_level' => ['required', Rule::enum(ExperienceLevel::class)],
            'training_days_per_week' => ['required', 'integer', 'between:1,7'],
            'preferred_units' => ['required', Rule::enum(WeightUnit::class)],
            'weight' => ['nullable', 'numeric', 'min:1', 'max:1000'],
            'height_cm' => ['nullable', 'integer', 'between:50,300'],
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fitness_goal' => 'objetivo',
            'experience_level' => 'nivel de experiencia',
            'training_days_per_week' => 'días de entrenamiento',
            'preferred_units' => 'unidades',
            'weight' => 'peso',
            'height_cm' => 'altura',
        ];
    }
}
