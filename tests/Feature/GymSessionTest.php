<?php

use App\Models\Exercise;
use App\Models\GymSession;
use App\Models\GymSessionExercise;
use App\Models\GymSet;
use App\Models\MuscleGroup;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanDay;
use App\Models\WorkoutPlanDayExercise;

test('guests cannot start gym sessions', function () {
    $this->post(route('gym-sessions.store'))->assertRedirect(route('login'));
});

test('users can start an empty gym session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('gym-sessions.store'), [])
        ->assertRedirect();

    expect(GymSession::query()->where('user_id', $user->id)->whereNull('ended_at')->exists())
        ->toBeTrue();
});

test('users can start session from workout plan day', function () {
    $user = User::factory()->create();
    $group = MuscleGroup::factory()->create();
    $exercise = Exercise::factory()->for($group, 'muscleGroup')->create();
    $plan = WorkoutPlan::factory()->for($user)->create();
    $day = WorkoutPlanDay::factory()->for($plan, 'workoutPlan')->create(['order' => 0]);
    WorkoutPlanDayExercise::factory()->for($day, 'workoutPlanDay')->for($exercise)->create();

    $this->actingAs($user)
        ->post(route('gym-sessions.store'), [
            'workout_plan_id' => $plan->id,
            'workout_plan_day_id' => $day->id,
        ])
        ->assertRedirect();

    $session = GymSession::query()
        ->where('user_id', $user->id)
        ->with('exercises.sets')
        ->first();

    expect($session)->not->toBeNull()
        ->and($session->exercises)->toHaveCount(1)
        ->and($session->exercises->first()->sets)->toHaveCount(1);
});

test('users can toggle gym set completion', function () {
    $user = User::factory()->create();
    $session = GymSession::factory()->for($user)->active()->create();
    $group = MuscleGroup::factory()->create();
    $exercise = Exercise::factory()->for($group, 'muscleGroup')->create();
    $sessionExercise = GymSessionExercise::factory()
        ->for($session, 'gymSession')
        ->for($exercise)
        ->create();
    $set = GymSet::factory()->for($sessionExercise, 'gymSessionExercise')->create([
        'completed' => false,
    ]);

    $this->actingAs($user)
        ->patch(route('gym-sets.toggle', $set))
        ->assertRedirect();

    expect($set->fresh()->completed)->toBeTrue();
});
