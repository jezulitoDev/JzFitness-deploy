<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\GymSession;
use App\Models\GymSessionExercise;
use App\Models\GymSet;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanDay;
use App\Services\WorkoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GymSessionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('gym-sessions/index', [
            'sessions' => $request->user()
                ->gymSessions()
                ->with('workoutPlan')
                ->latest('started_at')
                ->limit(30)
                ->get(),
            'workoutPlans' => $request->user()
                ->workoutPlans()
                ->with('days')
                ->get(),
            'activeSession' => $request->user()
                ->gymSessions()
                ->whereNull('ended_at')
                ->first(),
        ]);
    }

    public function store(Request $request, WorkoutService $workoutService): RedirectResponse
    {
        $validated = $request->validate([
            'workout_plan_id' => ['nullable', 'exists:workout_plans,id'],
            'workout_plan_day_id' => ['nullable', 'exists:workout_plan_days,id'],
        ]);

        $plan = null;
        $day = null;

        if (! empty($validated['workout_plan_id'])) {
            $plan = WorkoutPlan::query()
                ->where('user_id', $request->user()->id)
                ->findOrFail($validated['workout_plan_id']);
        }

        if (! empty($validated['workout_plan_day_id'])) {
            $day = WorkoutPlanDay::query()->findOrFail($validated['workout_plan_day_id']);

            if ($plan === null) {
                $plan = $day->workoutPlan;
            }

            if ($plan->user_id !== $request->user()->id) {
                abort(403);
            }
        }

        $session = $workoutService->startSession($request->user(), $plan, $day);

        return to_route('gym-sessions.play', $session);
    }

    public function show(Request $request, GymSession $gymSession): Response
    {
        $this->authorizeSession($request, $gymSession);

        $gymSession->load(['exercises.exercise.muscleGroup', 'exercises.sets', 'workoutPlan']);

        return Inertia::render('gym-sessions/show', [
            'session' => $gymSession,
            'stats' => app(WorkoutService::class)->sessionStats($gymSession),
        ]);
    }

    public function play(Request $request, GymSession $gymSession): Response
    {
        $this->authorizeSession($request, $gymSession);

        $gymSession->load(['exercises.exercise.muscleGroup', 'exercises.sets']);

        return Inertia::render('gym-sessions/play', [
            'session' => $gymSession,
            'exercises' => Exercise::query()->with('muscleGroup')->orderBy('name')->get(),
        ]);
    }

    public function finish(Request $request, GymSession $gymSession, WorkoutService $workoutService): RedirectResponse
    {
        $this->authorizeSession($request, $gymSession);

        $workoutService->finishSession($gymSession);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout completed.')]);

        return to_route('gym-sessions.show', $gymSession);
    }

    public function storeExercise(Request $request, GymSession $gymSession): RedirectResponse
    {
        $this->authorizeSession($request, $gymSession);

        if (! $gymSession->isActive()) {
            return back();
        }

        $validated = $request->validate([
            'exercise_id' => ['required', 'exists:exercises,id'],
            'default_rest_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
        ]);

        $maxOrder = $gymSession->exercises()->max('order') ?? -1;

        $sessionExercise = GymSessionExercise::query()->create([
            'gym_session_id' => $gymSession->id,
            'exercise_id' => $validated['exercise_id'],
            'order' => $maxOrder + 1,
            'default_rest_seconds' => $validated['default_rest_seconds'] ?? 90,
        ]);

        GymSet::query()->create([
            'gym_session_exercise_id' => $sessionExercise->id,
            'weight' => 0,
            'reps' => 0,
            'rest_seconds' => $validated['default_rest_seconds'] ?? 90,
            'completed' => false,
        ]);

        return back();
    }

    protected function authorizeSession(Request $request, GymSession $gymSession): void
    {
        if ($gymSession->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
