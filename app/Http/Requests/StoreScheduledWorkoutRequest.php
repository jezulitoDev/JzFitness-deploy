<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduledWorkoutRequest extends FormRequest
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
            'scheduled_date' => ['required', 'date'],
            'workout_plan_id' => ['nullable', 'integer', 'exists:workout_plans,id'],
            'workout_plan_day_id' => ['nullable', 'integer', 'exists:workout_plan_days,id'],
            'title' => ['nullable', 'required_without:workout_plan_id', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
