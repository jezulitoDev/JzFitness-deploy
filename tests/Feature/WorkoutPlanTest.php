<?php

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanDay;
use App\Models\WorkoutPlanDayExercise;

test('guests cannot access workout plans', function () {
    $this->get(route('workout-plans.index'))->assertRedirect(route('login'));
});

test('users can list their active and archived plans separately', function () {
    $user = User::factory()->create();
    WorkoutPlan::factory()->for($user)->create();
    WorkoutPlan::factory()->for($user)->create(['archived_at' => now()]);

    $this->actingAs($user)
        ->get(route('workout-plans.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('workout-plans/index')
            ->has('workoutPlans', 1)
            ->has('archivedPlans', 1));
});

test('users can create a workout plan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('workout-plans.store'), [
            'name' => 'Push Pull Legs',
            'description' => 'Rutina de 3 días',
        ])
        ->assertRedirect();

    expect($user->workoutPlans()->where('name', 'Push Pull Legs')->exists())->toBeTrue();
});

test('users can update their workout plan', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('workout-plans.update', $plan), [
            'name' => 'Nuevo nombre',
        ])
        ->assertRedirect(route('workout-plans.show', $plan));

    expect($plan->fresh()->name)->toBe('Nuevo nombre');
});

test('users can delete their workout plan', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('workout-plans.destroy', $plan))
        ->assertRedirect(route('workout-plans.index'));

    $this->assertModelMissing($plan);
});

test('users can duplicate a plan including days and exercises with targets', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create(['name' => 'Original']);
    $day = WorkoutPlanDay::factory()->for($plan, 'workoutPlan')->create(['name' => 'Push', 'order' => 0]);
    $exercise = Exercise::factory()->for(MuscleGroup::factory(), 'muscleGroup')->create();
    WorkoutPlanDayExercise::factory()
        ->for($day, 'workoutPlanDay')
        ->for($exercise, 'exercise')
        ->create(['target_sets' => 4, 'target_reps' => 10, 'target_weight' => 80]);

    $this->actingAs($user)
        ->post(route('workout-plans.duplicate', $plan))
        ->assertRedirect();

    $copy = $user->workoutPlans()->where('name', 'like', '%copia%')->first();

    expect($copy)->not->toBeNull()
        ->and($copy->days)->toHaveCount(1)
        ->and($copy->days->first()->exercises)->toHaveCount(1)
        ->and($copy->days->first()->exercises->first()->target_sets)->toBe(4)
        ->and($copy->days->first()->exercises->first()->target_weight)->toBe(80.0);
});

test('users can archive and restore a plan', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();

    $this->actingAs($user)->patch(route('workout-plans.archive', $plan));
    expect($plan->fresh()->isArchived())->toBeTrue();

    $this->actingAs($user)->patch(route('workout-plans.archive', $plan));
    expect($plan->fresh()->isArchived())->toBeFalse();
});

test('users can add a day exercise with targets', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();
    $day = WorkoutPlanDay::factory()->for($plan, 'workoutPlan')->create();
    $exercise = Exercise::factory()->for(MuscleGroup::factory(), 'muscleGroup')->create();

    $this->actingAs($user)
        ->post(route('workout-plans.days.exercises.store', [$plan, $day]), [
            'exercise_id' => $exercise->id,
            'target_sets' => 3,
            'target_reps' => 12,
            'target_weight' => 60.5,
        ])
        ->assertRedirect();

    $dayExercise = $day->exercises()->first();

    expect($dayExercise->target_sets)->toBe(3)
        ->and($dayExercise->target_reps)->toBe(12)
        ->and($dayExercise->target_weight)->toBe(60.5);
});

test('users can update targets of a day exercise', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();
    $day = WorkoutPlanDay::factory()->for($plan, 'workoutPlan')->create();
    $dayExercise = WorkoutPlanDayExercise::factory()
        ->for($day, 'workoutPlanDay')
        ->for(Exercise::factory()->for(MuscleGroup::factory(), 'muscleGroup'), 'exercise')
        ->create();

    $this->actingAs($user)
        ->patch(route('workout-plans.days.exercises.update', [$plan, $day, $dayExercise]), [
            'target_sets' => 5,
            'target_reps' => 5,
            'target_weight' => 100,
            'default_rest_seconds' => 180,
        ])
        ->assertRedirect();

    $dayExercise->refresh();

    expect($dayExercise->target_sets)->toBe(5)
        ->and($dayExercise->default_rest_seconds)->toBe(180);
});

test('users can reorder day exercises', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();
    $day = WorkoutPlanDay::factory()->for($plan, 'workoutPlan')->create();
    $group = MuscleGroup::factory()->create();
    $first = WorkoutPlanDayExercise::factory()
        ->for($day, 'workoutPlanDay')
        ->for(Exercise::factory()->for($group, 'muscleGroup'), 'exercise')
        ->create(['position' => 0]);
    $second = WorkoutPlanDayExercise::factory()
        ->for($day, 'workoutPlanDay')
        ->for(Exercise::factory()->for($group, 'muscleGroup'), 'exercise')
        ->create(['position' => 1]);

    $this->actingAs($user)
        ->patch(route('workout-plans.days.exercises.reorder', [$plan, $day]), [
            'exercise_ids' => [$second->id, $first->id],
        ])
        ->assertRedirect();

    expect($second->fresh()->position)->toBe(0)
        ->and($first->fresh()->position)->toBe(1);
});

test('users can reorder plan days', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();
    $firstDay = WorkoutPlanDay::factory()->for($plan, 'workoutPlan')->create(['order' => 0]);
    $secondDay = WorkoutPlanDay::factory()->for($plan, 'workoutPlan')->create(['order' => 1]);

    $this->actingAs($user)
        ->patch(route('workout-plans.days.reorder', $plan), [
            'day_ids' => [$secondDay->id, $firstDay->id],
        ])
        ->assertRedirect();

    expect($secondDay->fresh()->order)->toBe(0)
        ->and($firstDay->fresh()->order)->toBe(1);
});

test('users cannot manage workout plans of other users', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($owner)->create();

    $this->actingAs($intruder)->get(route('workout-plans.show', $plan))->assertForbidden();
    $this->actingAs($intruder)->patch(route('workout-plans.update', $plan), ['name' => 'X'])->assertForbidden();
    $this->actingAs($intruder)->post(route('workout-plans.duplicate', $plan))->assertForbidden();
    $this->actingAs($intruder)->patch(route('workout-plans.archive', $plan))->assertForbidden();
    $this->actingAs($intruder)->delete(route('workout-plans.destroy', $plan))->assertForbidden();
});
