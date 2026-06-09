<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    public function index(Request $request): Response
    {
        $muscleGroupId = $request->integer('muscle_group_id');
        $search = $request->string('search')->trim()->toString();

        $exercises = Exercise::query()
            ->visibleTo($request->user())
            ->with('muscleGroup')
            ->when($muscleGroupId, fn ($q) => $q->where('muscle_group_id', $muscleGroupId))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return Inertia::render('exercises/index', [
            'exercises' => $exercises,
            'muscleGroups' => MuscleGroup::query()->orderBy('name')->get(),
            'filters' => [
                'muscle_group_id' => $muscleGroupId ?: null,
                'search' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('exercises/create', [
            'muscleGroups' => MuscleGroup::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreExerciseRequest $request): RedirectResponse
    {
        $request->user()->exercises()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise created.')]);

        return to_route('exercises.index');
    }

    public function edit(Request $request, Exercise $exercise): Response
    {
        $this->authorizeExercise($request, $exercise);

        return Inertia::render('exercises/edit', [
            'exercise' => $exercise->load('muscleGroup'),
            'muscleGroups' => MuscleGroup::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $this->authorizeExercise($request, $exercise);

        $exercise->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise updated.')]);

        return to_route('exercises.index');
    }

    public function destroy(Request $request, Exercise $exercise): RedirectResponse
    {
        $this->authorizeExercise($request, $exercise);

        $exercise->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Exercise deleted.')]);

        return to_route('exercises.index');
    }

    /**
     * Only the owner can manage a custom exercise; global ones are read-only.
     */
    protected function authorizeExercise(Request $request, Exercise $exercise): void
    {
        if (! $exercise->isOwnedBy($request->user())) {
            abort(403);
        }
    }
}
