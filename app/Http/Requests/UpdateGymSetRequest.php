<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGymSetRequest extends FormRequest
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
            'weight' => ['sometimes', 'numeric', 'min:0'],
            'reps' => ['sometimes', 'integer', 'min:0'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'rest_seconds' => ['sometimes', 'integer', 'min:0', 'max:600'],
            'rpe' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'completed' => ['sometimes', 'boolean'],
        ];
    }
}
