<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutPlanRequest;
use App\Http\Requests\UpdateWorkoutPlanRequest;
use App\Models\Exercise;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanDay;
use App\Models\WorkoutPlanDayExercise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkoutPlanController extends Controller
{
    public function index(Request $request): Response
    {
        $plans = $request->user()
            ->workoutPlans()
            ->withCount('days')
            ->latest()
            ->get();

        return Inertia::render('workout-plans/index', [
            'workoutPlans' => $plans->whereNull('archived_at')->values(),
            'archivedPlans' => $plans->whereNotNull('archived_at')->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('workout-plans/create');
    }

    public function store(StoreWorkoutPlanRequest $request): RedirectResponse
    {
        $plan = $request->user()->workoutPlans()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout plan created.')]);

        return to_route('workout-plans.show', $plan);
    }

    public function show(Request $request, WorkoutPlan $workoutPlan): Response
    {
        $this->authorizePlan($request, $workoutPlan);

        $workoutPlan->load([
            'days.exercises.exercise.muscleGroup',
        ]);

        return Inertia::render('workout-plans/show', [
            'workoutPlan' => $workoutPlan,
            'exercises' => Exercise::query()
                ->visibleTo($request->user())
                ->with('muscleGroup')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(Request $request, WorkoutPlan $workoutPlan): Response
    {
        $this->authorizePlan($request, $workoutPlan);

        return Inertia::render('workout-plans/edit', [
            'workoutPlan' => $workoutPlan,
        ]);
    }

    public function update(UpdateWorkoutPlanRequest $request, WorkoutPlan $workoutPlan): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);

        $workoutPlan->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout plan updated.')]);

        return to_route('workout-plans.show', $workoutPlan);
    }

    public function duplicate(Request $request, WorkoutPlan $workoutPlan): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);

        $workoutPlan->load('days.exercises');

        $copy = $workoutPlan->replicate(['archived_at']);
        $copy->name = __(':name (copia)', ['name' => $workoutPlan->name]);
        $copy->save();

        foreach ($workoutPlan->days as $day) {
            $newDay = $copy->days()->create([
                'name' => $day->name,
                'order' => $day->order,
            ]);

            foreach ($day->exercises as $dayExercise) {
                $newDay->exercises()->create([
                    'exercise_id' => $dayExercise->exercise_id,
                    'position' => $dayExercise->position,
                    'default_rest_seconds' => $dayExercise->default_rest_seconds,
                    'target_sets' => $dayExercise->target_sets,
                    'target_reps' => $dayExercise->target_reps,
                    'target_weight' => $dayExercise->target_weight,
                ]);
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout plan duplicated.')]);

        return to_route('workout-plans.show', $copy);
    }

    public function archive(Request $request, WorkoutPlan $workoutPlan): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);

        $workoutPlan->update([
            'archived_at' => $workoutPlan->isArchived() ? null : now(),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $workoutPlan->isArchived()
                ? __('Workout plan archived.')
                : __('Workout plan restored.'),
        ]);

        return back();
    }

    public function destroy(Request $request, WorkoutPlan $workoutPlan): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);

        $workoutPlan->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout plan deleted.')]);

        return to_route('workout-plans.index');
    }

    public function storeDay(Request $request, WorkoutPlan $workoutPlan): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $maxOrder = $workoutPlan->days()->max('order') ?? -1;

        $workoutPlan->days()->create([
            'name' => $validated['name'],
            'order' => $maxOrder + 1,
        ]);

        return back();
    }

    public function updateDay(Request $request, WorkoutPlan $workoutPlan, WorkoutPlanDay $day): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);
        $this->authorizeDay($workoutPlan, $day);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $day->update($validated);

        return back();
    }

    public function destroyDay(Request $request, WorkoutPlan $workoutPlan, WorkoutPlanDay $day): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);
        $this->authorizeDay($workoutPlan, $day);

        $day->delete();

        return back();
    }

    public function storeDayExercise(Request $request, WorkoutPlan $workoutPlan, WorkoutPlanDay $day): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);
        $this->authorizeDay($workoutPlan, $day);

        $validated = $request->validate([
            'exercise_id' => ['required', 'exists:exercises,id'],
            'default_rest_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
            'target_sets' => ['nullable', 'integer', 'min:1', 'max:20'],
            'target_reps' => ['nullable', 'integer', 'min:1', 'max:100'],
            'target_weight' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ]);

        $maxPosition = $day->exercises()->max('position') ?? -1;

        $day->exercises()->create([
            'exercise_id' => $validated['exercise_id'],
            'position' => $maxPosition + 1,
            'default_rest_seconds' => $validated['default_rest_seconds'] ?? 90,
            'target_sets' => $validated['target_sets'] ?? null,
            'target_reps' => $validated['target_reps'] ?? null,
            'target_weight' => $validated['target_weight'] ?? null,
        ]);

        return back();
    }

    public function updateDayExercise(
        Request $request,
        WorkoutPlan $workoutPlan,
        WorkoutPlanDay $day,
        WorkoutPlanDayExercise $dayExercise,
    ): RedirectResponse {
        $this->authorizePlan($request, $workoutPlan);
        $this->authorizeDay($workoutPlan, $day);

        if ($dayExercise->workout_plan_day_id !== $day->id) {
            abort(404);
        }

        $validated = $request->validate([
            'default_rest_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
            'target_sets' => ['nullable', 'integer', 'min:1', 'max:20'],
            'target_reps' => ['nullable', 'integer', 'min:1', 'max:100'],
            'target_weight' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ]);

        $dayExercise->update($validated);

        return back();
    }

    public function reorderDayExercises(Request $request, WorkoutPlan $workoutPlan, WorkoutPlanDay $day): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);
        $this->authorizeDay($workoutPlan, $day);

        $validated = $request->validate([
            'exercise_ids' => ['required', 'array'],
            'exercise_ids.*' => ['integer', 'exists:workout_plan_day_exercises,id'],
        ]);

        foreach ($validated['exercise_ids'] as $position => $dayExerciseId) {
            WorkoutPlanDayExercise::query()
                ->where('workout_plan_day_id', $day->id)
                ->where('id', $dayExerciseId)
                ->update(['position' => $position]);
        }

        return back();
    }

    public function destroyDayExercise(
        Request $request,
        WorkoutPlan $workoutPlan,
        WorkoutPlanDay $day,
        WorkoutPlanDayExercise $dayExercise,
    ): RedirectResponse {
        $this->authorizePlan($request, $workoutPlan);
        $this->authorizeDay($workoutPlan, $day);

        if ($dayExercise->workout_plan_day_id !== $day->id) {
            abort(404);
        }

        $dayExercise->delete();

        return back();
    }

    public function reorderDays(Request $request, WorkoutPlan $workoutPlan): RedirectResponse
    {
        $this->authorizePlan($request, $workoutPlan);

        $validated = $request->validate([
            'day_ids' => ['required', 'array'],
            'day_ids.*' => ['integer', 'exists:workout_plan_days,id'],
        ]);

        foreach ($validated['day_ids'] as $order => $dayId) {
            WorkoutPlanDay::query()
                ->where('workout_plan_id', $workoutPlan->id)
                ->where('id', $dayId)
                ->update(['order' => $order]);
        }

        return back();
    }

    protected function authorizePlan(Request $request, WorkoutPlan $workoutPlan): void
    {
        if ($workoutPlan->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    protected function authorizeDay(WorkoutPlan $workoutPlan, WorkoutPlanDay $day): void
    {
        if ($day->workout_plan_id !== $workoutPlan->id) {
            abort(404);
        }
    }
}
