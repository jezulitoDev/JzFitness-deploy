<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduledWorkoutRequest;
use App\Http\Requests\UpdateScheduledWorkoutRequest;
use App\Models\ScheduledWorkout;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduledWorkoutController extends Controller
{
    public function store(StoreScheduledWorkoutRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->ensurePlanAndDayBelongToUser($request, $validated);

        $request->user()->scheduledWorkouts()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workout scheduled.')]);

        return back();
    }

    public function update(UpdateScheduledWorkoutRequest $request, ScheduledWorkout $scheduledWorkout): RedirectResponse
    {
        $this->authorizeScheduledWorkout($request, $scheduledWorkout);

        $validated = $request->validated();

        if (array_key_exists('completed', $validated)) {
            $scheduledWorkout->completed_at = $validated['completed'] ? now() : null;
            unset($validated['completed']);
        }

        $scheduledWorkout->fill($validated);
        $scheduledWorkout->save();

        return back();
    }

    public function destroy(Request $request, ScheduledWorkout $scheduledWorkout): RedirectResponse
    {
        $this->authorizeScheduledWorkout($request, $scheduledWorkout);

        $scheduledWorkout->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Scheduled workout removed.')]);

        return back();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function ensurePlanAndDayBelongToUser(Request $request, array $validated): void
    {
        $plan = null;

        if (! empty($validated['workout_plan_id'])) {
            $plan = WorkoutPlan::query()->findOrFail($validated['workout_plan_id']);

            if ($plan->user_id !== $request->user()->id) {
                abort(403);
            }
        }

        if (! empty($validated['workout_plan_day_id'])) {
            $day = WorkoutPlanDay::query()->findOrFail($validated['workout_plan_day_id']);

            if ($plan === null || $day->workout_plan_id !== $plan->id) {
                abort(422);
            }
        }
    }

    protected function authorizeScheduledWorkout(Request $request, ScheduledWorkout $scheduledWorkout): void
    {
        if ($scheduledWorkout->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
