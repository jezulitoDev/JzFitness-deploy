<?php

use App\Models\ScheduledWorkout;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanDay;

test('users can schedule a custom workout on a date', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('scheduled-workouts.store'), [
            'scheduled_date' => now()->addDay()->toDateString(),
            'title' => 'Cardio suave',
        ])
        ->assertRedirect();

    expect($user->scheduledWorkouts()->where('title', 'Cardio suave')->exists())->toBeTrue();
});

test('users can schedule a workout plan day', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();
    $day = WorkoutPlanDay::factory()->for($plan, 'workoutPlan')->create();

    $this->actingAs($user)
        ->post(route('scheduled-workouts.store'), [
            'scheduled_date' => now()->toDateString(),
            'workout_plan_id' => $plan->id,
            'workout_plan_day_id' => $day->id,
        ])
        ->assertRedirect();

    $scheduled = $user->scheduledWorkouts()->first();

    expect($scheduled->workout_plan_id)->toBe($plan->id)
        ->and($scheduled->workout_plan_day_id)->toBe($day->id);
});

test('a title is required when scheduling without a plan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('scheduled-workouts.store'), [
            'scheduled_date' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('title');
});

test('users cannot schedule another users workout plan', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->post(route('scheduled-workouts.store'), [
            'scheduled_date' => now()->toDateString(),
            'workout_plan_id' => $plan->id,
        ])
        ->assertForbidden();
});

test('scheduling fails when the day does not belong to the plan', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();
    $otherPlan = WorkoutPlan::factory()->for($user)->create();
    $day = WorkoutPlanDay::factory()->for($otherPlan, 'workoutPlan')->create();

    $this->actingAs($user)
        ->post(route('scheduled-workouts.store'), [
            'scheduled_date' => now()->toDateString(),
            'workout_plan_id' => $plan->id,
            'workout_plan_day_id' => $day->id,
        ])
        ->assertUnprocessable();
});

test('users can mark a scheduled workout as completed and pending again', function () {
    $user = User::factory()->create();
    $scheduled = ScheduledWorkout::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('scheduled-workouts.update', $scheduled), ['completed' => true])
        ->assertRedirect();

    expect($scheduled->fresh()->isCompleted())->toBeTrue();

    $this->actingAs($user)
        ->patch(route('scheduled-workouts.update', $scheduled), ['completed' => false]);

    expect($scheduled->fresh()->isCompleted())->toBeFalse();
});

test('users can reschedule a workout to another date', function () {
    $user = User::factory()->create();
    $scheduled = ScheduledWorkout::factory()->for($user)->create([
        'scheduled_date' => now()->toDateString(),
    ]);

    $newDate = now()->addDays(3)->toDateString();

    $this->actingAs($user)
        ->patch(route('scheduled-workouts.update', $scheduled), [
            'scheduled_date' => $newDate,
        ])
        ->assertRedirect();

    expect($scheduled->fresh()->scheduled_date->toDateString())->toBe($newDate);
});

test('users can delete a scheduled workout', function () {
    $user = User::factory()->create();
    $scheduled = ScheduledWorkout::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('scheduled-workouts.destroy', $scheduled))
        ->assertRedirect();

    $this->assertModelMissing($scheduled);
});

test('users cannot manage scheduled workouts of other users', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $scheduled = ScheduledWorkout::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->patch(route('scheduled-workouts.update', $scheduled), ['completed' => true])
        ->assertForbidden();

    $this->actingAs($intruder)
        ->delete(route('scheduled-workouts.destroy', $scheduled))
        ->assertForbidden();
});

test('the calendar shows scheduled workouts as planned events', function () {
    $user = User::factory()->create();
    $date = now()->startOfMonth()->addDays(5);

    ScheduledWorkout::factory()->for($user)->create([
        'scheduled_date' => $date->toDateString(),
        'title' => 'Full body',
    ]);

    $this->actingAs($user)
        ->get(route('calendar.index', ['year' => $date->year, 'month' => $date->month]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('calendar/index')
            ->where('events.'.$date->toDateString().'.0.type', 'planned')
            ->where('events.'.$date->toDateString().'.0.name', 'Full body'));
});

test('the calendar supports a week view', function () {
    $user = User::factory()->create();
    $weekStart = now()->startOfWeek();

    ScheduledWorkout::factory()->for($user)->create([
        'scheduled_date' => $weekStart->copy()->addDays(2)->toDateString(),
        'title' => 'Push',
    ]);

    $this->actingAs($user)
        ->get(route('calendar.index', ['view' => 'week', 'week_start' => $weekStart->toDateString()]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('calendar/index')
            ->where('view', 'week')
            ->where('weekStart', $weekStart->toDateString())
            ->has('events'));
});
