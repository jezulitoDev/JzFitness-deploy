<?php

use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\User;
use Database\Seeders\ExerciseSeeder;
use Database\Seeders\MuscleGroupSeeder;

test('the muscle group seeder covers every expected group', function () {
    $this->seed(MuscleGroupSeeder::class);

    $expected = [
        'Pecho', 'Espalda', 'Piernas', 'Hombros', 'Biceps', 'Triceps', 'Core',
        'Antebrazos', 'Cuádriceps', 'Isquiotibiales', 'Glúteos', 'Gemelos',
        'Trapecio', 'Lumbar', 'Cuerpo completo', 'Cardio',
    ];

    $names = MuscleGroup::query()->pluck('name')->all();

    foreach ($expected as $name) {
        expect($names)->toContain($name);
    }
});

test('the exercise seeder populates a complete global catalog', function () {
    $this->seed(MuscleGroupSeeder::class);
    $this->seed(ExerciseSeeder::class);

    expect(Exercise::query()->whereNull('user_id')->count())->toBeGreaterThanOrEqual(120);

    $coveredGroups = MuscleGroup::query()
        ->whereHas('exercises', fn ($query) => $query->whereNull('user_id'))
        ->pluck('name')
        ->all();

    foreach (['Pecho', 'Espalda', 'Hombros', 'Biceps', 'Triceps', 'Antebrazos', 'Cuádriceps', 'Isquiotibiales', 'Glúteos', 'Gemelos', 'Core', 'Trapecio', 'Lumbar', 'Cuerpo completo', 'Cardio'] as $group) {
        expect($coveredGroups)->toContain($group);
    }
});

test('every seeded exercise has equipment and a description', function () {
    $this->seed(MuscleGroupSeeder::class);
    $this->seed(ExerciseSeeder::class);

    expect(Exercise::query()->whereNull('user_id')->whereNull('equipment')->count())->toBe(0)
        ->and(Exercise::query()->whereNull('user_id')->whereNull('description')->count())->toBe(0);
});

test('the exercise seeder is idempotent and keeps existing records', function () {
    $this->seed(MuscleGroupSeeder::class);
    $this->seed(ExerciseSeeder::class);

    $count = Exercise::count();
    $pressBancaId = Exercise::query()->whereNull('user_id')->where('name', 'Press banca')->sole()->id;

    $this->seed(ExerciseSeeder::class);

    expect(Exercise::count())->toBe($count)
        ->and(Exercise::query()->whereNull('user_id')->where('name', 'Press banca')->sole()->id)->toBe($pressBancaId);
});

test('the exercise seeder updates legacy global exercises instead of duplicating them', function () {
    $this->seed(MuscleGroupSeeder::class);

    $legacyGroup = MuscleGroup::query()->where('name', 'Piernas')->sole();
    $legacy = Exercise::factory()->create([
        'user_id' => null,
        'muscle_group_id' => $legacyGroup->id,
        'name' => 'Sentadilla',
    ]);

    $this->seed(ExerciseSeeder::class);

    $sentadillas = Exercise::query()->whereNull('user_id')->where('name', 'Sentadilla')->get();

    expect($sentadillas)->toHaveCount(1)
        ->and($sentadillas->first()->id)->toBe($legacy->id)
        ->and($sentadillas->first()->muscleGroup->name)->toBe('Cuádriceps');
});

test('the exercise seeder does not touch custom user exercises', function () {
    $this->seed(MuscleGroupSeeder::class);

    $user = User::factory()->create();
    $group = MuscleGroup::query()->where('name', 'Pecho')->sole();
    $custom = Exercise::factory()->create([
        'user_id' => $user->id,
        'muscle_group_id' => $group->id,
        'name' => 'Press banca',
        'description' => 'Mi variante personal',
    ]);

    $this->seed(ExerciseSeeder::class);

    $custom->refresh();

    expect($custom->description)->toBe('Mi variante personal')
        ->and($custom->user_id)->toBe($user->id)
        ->and(Exercise::query()->where('name', 'Press banca')->count())->toBe(2);
});
