<?php

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\User;

test('guests cannot access exercises', function () {
    $this->get(route('exercises.index'))->assertRedirect(route('login'));
});

test('authenticated users can list exercises', function () {
    $user = User::factory()->create();
    $group = MuscleGroup::factory()->create();
    Exercise::factory()->for($group, 'muscleGroup')->create();

    $this->actingAs($user)
        ->get(route('exercises.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('exercises/index')
            ->has('exercises', 1));
});

test('authenticated users can create exercises', function () {
    $user = User::factory()->create();
    $group = MuscleGroup::factory()->create();

    $this->actingAs($user)
        ->post(route('exercises.store'), [
            'muscle_group_id' => $group->id,
            'name' => 'Press banca',
            'equipment' => 'Barbell',
        ])
        ->assertRedirect(route('exercises.index'));

    expect(Exercise::query()->where('name', 'Press banca')->exists())->toBeTrue();
});

test('created exercises belong to the authenticated user', function () {
    $user = User::factory()->create();
    $group = MuscleGroup::factory()->create();

    $this->actingAs($user)
        ->post(route('exercises.store'), [
            'muscle_group_id' => $group->id,
            'name' => 'Mi ejercicio custom',
            'description' => 'Instrucciones del ejercicio',
        ]);

    expect($user->exercises()->where('name', 'Mi ejercicio custom')->exists())->toBeTrue();
});

test('users see global exercises and their own but not those of other users', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $group = MuscleGroup::factory()->create();

    Exercise::factory()->for($group, 'muscleGroup')->create(['name' => 'Global']);
    Exercise::factory()->for($group, 'muscleGroup')->for($user)->create(['name' => 'Mío']);
    Exercise::factory()->for($group, 'muscleGroup')->for($other)->create(['name' => 'Ajeno']);

    $this->actingAs($user)
        ->get(route('exercises.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('exercises', 2));
});

test('exercises can be searched by name', function () {
    $user = User::factory()->create();
    $group = MuscleGroup::factory()->create();

    Exercise::factory()->for($group, 'muscleGroup')->create(['name' => 'Press banca']);
    Exercise::factory()->for($group, 'muscleGroup')->create(['name' => 'Sentadilla']);

    $this->actingAs($user)
        ->get(route('exercises.index', ['search' => 'banca']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('exercises', 1));
});

test('exercises can be filtered by muscle group', function () {
    $user = User::factory()->create();
    $chest = MuscleGroup::factory()->create();
    $legs = MuscleGroup::factory()->create();

    Exercise::factory()->for($chest, 'muscleGroup')->create();
    Exercise::factory()->for($legs, 'muscleGroup')->create();

    $this->actingAs($user)
        ->get(route('exercises.index', ['muscle_group_id' => $chest->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('exercises', 1));
});

test('owners can update and delete their custom exercises', function () {
    $user = User::factory()->create();
    $group = MuscleGroup::factory()->create();
    $exercise = Exercise::factory()->for($group, 'muscleGroup')->for($user)->create();

    $this->actingAs($user)
        ->patch(route('exercises.update', $exercise), [
            'muscle_group_id' => $group->id,
            'name' => 'Nombre nuevo',
        ])
        ->assertRedirect(route('exercises.index'));

    expect($exercise->fresh()->name)->toBe('Nombre nuevo');

    $this->actingAs($user)
        ->delete(route('exercises.destroy', $exercise))
        ->assertRedirect(route('exercises.index'));

    $this->assertModelMissing($exercise);
});

test('users cannot edit global exercises', function () {
    $user = User::factory()->create();
    $group = MuscleGroup::factory()->create();
    $exercise = Exercise::factory()->for($group, 'muscleGroup')->create();

    $this->actingAs($user)->get(route('exercises.edit', $exercise))->assertForbidden();
    $this->actingAs($user)
        ->patch(route('exercises.update', $exercise), [
            'muscle_group_id' => $group->id,
            'name' => 'Hack',
        ])
        ->assertForbidden();
    $this->actingAs($user)->delete(route('exercises.destroy', $exercise))->assertForbidden();
});

test('users cannot manage custom exercises of other users', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $group = MuscleGroup::factory()->create();
    $exercise = Exercise::factory()->for($group, 'muscleGroup')->for($owner)->create();

    $this->actingAs($intruder)->get(route('exercises.edit', $exercise))->assertForbidden();
    $this->actingAs($intruder)->delete(route('exercises.destroy', $exercise))->assertForbidden();
});
