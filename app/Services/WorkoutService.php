<?php

namespace App\Services;

use App\Models\GymSession;
use App\Models\GymSessionExercise;
use App\Models\GymSet;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanDay;
use Illuminate\Support\Carbon;

class WorkoutService
{
    public function startSession(
        User $user,
        ?WorkoutPlan $plan = null,
        ?WorkoutPlanDay $day = null,
    ): GymSession {
        $session = GymSession::query()->create([
            'user_id' => $user->id,
            'workout_plan_id' => $plan?->id,
            'started_at' => now(),
            'ended_at' => null,
            'notes' => null,
        ]);

        if ($day !== null) {
            foreach ($day->exercises()->with('exercise')->get() as $planExercise) {
                $sessionExercise = GymSessionExercise::query()->create([
                    'gym_session_id' => $session->id,
                    'exercise_id' => $planExercise->exercise_id,
                    'order' => $planExercise->position,
                    'default_rest_seconds' => $planExercise->default_rest_seconds,
                ]);

                GymSet::query()->create([
                    'gym_session_exercise_id' => $sessionExercise->id,
                    'weight' => 0,
                    'reps' => 0,
                    'rest_seconds' => $planExercise->default_rest_seconds,
                    'completed' => false,
                ]);
            }
        }

        return $session->load(['exercises.exercise', 'exercises.sets']);
    }

    public function finishSession(GymSession $session): GymSession
    {
        $session->update([
            'ended_at' => $session->ended_at ?? now(),
        ]);

        return $session->fresh();
    }

    /**
     * @return array{volume: float, sets: int, duration_minutes: int}
     */
    public function sessionStats(GymSession $session): array
    {
        $session->load(['exercises.sets']);

        $volume = 0.0;
        $sets = 0;

        foreach ($session->exercises as $exercise) {
            foreach ($exercise->sets as $set) {
                if ($set->completed) {
                    $volume += $set->volume();
                    $sets++;
                }
            }
        }

        $endedAt = $session->ended_at ?? now();
        $durationMinutes = (int) Carbon::parse($session->started_at)->diffInMinutes($endedAt);

        return [
            'volume' => $volume,
            'sets' => $sets,
            'duration_minutes' => max($durationMinutes, 1),
        ];
    }
}
