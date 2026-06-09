<?php

namespace App\Http\Controllers\Settings;

use App\Enums\ExperienceLevel;
use App\Enums\FitnessGoal;
use App\Enums\WeightUnit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\FitnessProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FitnessProfileController extends Controller
{
    /**
     * Show the user's fitness profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $units = $user->preferred_units ?? WeightUnit::Kilograms;

        return Inertia::render('settings/fitness', [
            'fitnessProfile' => [
                'fitness_goal' => $user->fitness_goal?->value,
                'experience_level' => $user->experience_level?->value,
                'training_days_per_week' => $user->training_days_per_week,
                'weight' => $user->weight_kg !== null ? $units->fromKilograms($user->weight_kg) : null,
                'height_cm' => $user->height_cm,
                'preferred_units' => $units->value,
            ],
            'goals' => collect(FitnessGoal::cases())
                ->map(fn (FitnessGoal $goal): array => ['value' => $goal->value, 'label' => $goal->label()])
                ->all(),
            'levels' => collect(ExperienceLevel::cases())
                ->map(fn (ExperienceLevel $level): array => ['value' => $level->value, 'label' => $level->label()])
                ->all(),
        ]);
    }

    /**
     * Update the user's fitness profile.
     */
    public function update(FitnessProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $units = WeightUnit::from($validated['preferred_units']);
        $weight = $validated['weight'] ?? null;

        $request->user()->update([
            'fitness_goal' => $validated['fitness_goal'],
            'experience_level' => $validated['experience_level'],
            'training_days_per_week' => $validated['training_days_per_week'],
            'preferred_units' => $units,
            'weight_kg' => $weight !== null ? $units->toKilograms((float) $weight) : null,
            'height_cm' => $validated['height_cm'] ?? null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Perfil fitness actualizado.')]);

        return to_route('fitness.edit');
    }
}
