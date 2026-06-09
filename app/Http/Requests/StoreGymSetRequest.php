<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGymSetRequest extends FormRequest
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
            'weight' => ['nullable', 'numeric', 'min:0'],
            'reps' => ['nullable', 'integer', 'min:0'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'rest_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
            'rpe' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ];
    }
}
