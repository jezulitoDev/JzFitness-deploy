<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGymSetRequest;
use App\Http\Requests\UpdateGymSetRequest;
use App\Models\GymSessionExercise;
use App\Models\GymSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GymSetController extends Controller
{
    public function store(StoreGymSetRequest $request, GymSessionExercise $gymSessionExercise): RedirectResponse
    {
        $this->authorizeExercise($request, $gymSessionExercise);

        $lastSet = $gymSessionExercise->sets()->latest('id')->first();

        $gymSessionExercise->sets()->create([
            'weight' => $request->input('weight', $lastSet?->weight ?? 0),
            'reps' => $request->input('reps', $lastSet?->reps ?? 0),
            'duration' => $request->input('duration'),
            'rest_seconds' => $request->input('rest_seconds', $gymSessionExercise->default_rest_seconds),
            'rpe' => $request->input('rpe'),
            'completed' => false,
        ]);

        return back();
    }

    public function update(UpdateGymSetRequest $request, GymSet $gymSet): RedirectResponse
    {
        $this->authorizeSet($request, $gymSet);

        $gymSet->update($request->validated());

        return back();
    }

    public function toggle(Request $request, GymSet $gymSet): RedirectResponse
    {
        $this->authorizeSet($request, $gymSet);

        $gymSet->update([
            'completed' => ! $gymSet->completed,
        ]);

        return back();
    }

    public function destroy(Request $request, GymSet $gymSet): RedirectResponse
    {
        $this->authorizeSet($request, $gymSet);

        $gymSet->delete();

        return back();
    }

    protected function authorizeExercise(Request $request, GymSessionExercise $exercise): void
    {
        $exercise->load('gymSession');

        if ($exercise->gymSession->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    protected function authorizeSet(Request $request, GymSet $gymSet): void
    {
        $gymSet->load('gymSessionExercise.gymSession');

        if ($gymSet->gymSessionExercise->gymSession->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
