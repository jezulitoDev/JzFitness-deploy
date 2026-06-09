<?php

use App\Enums\ExperienceLevel;
use App\Enums\FitnessGoal;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('fitness.edit'))->assertRedirect(route('login'));
});

test('fitness profile page is displayed with current values and options', function () {
    $user = User::factory()->withFitnessProfile()->create([
        'fitness_goal' => FitnessGoal::GainMuscle,
        'preferred_units' => 'kg',
        'weight_kg' => 80.5,
    ]);

    $this->actingAs($user)
        ->get(route('fitness.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/fitness')
            ->where('fitnessProfile.fitness_goal', 'gain_muscle')
            ->where('fitnessProfile.weight', 80.5)
            ->has('goals', 4)
            ->has('levels', 3));
});

test('fitness profile can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('fitness.update'), [
            'fitness_goal' => 'lose_weight',
            'experience_level' => 'beginner',
            'training_days_per_week' => 4,
            'preferred_units' => 'kg',
            'weight' => 82.5,
            'height_cm' => 178,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('fitness.edit'));

    $user->refresh();

    expect($user->fitness_goal)->toBe(FitnessGoal::LoseWeight);
    expect($user->experience_level)->toBe(ExperienceLevel::Beginner);
    expect($user->training_days_per_week)->toBe(4);
    expect($user->weight_kg)->toBe(82.5);
    expect($user->height_cm)->toBe(178);
});

test('weight is converted to kilograms when pounds are preferred', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('fitness.update'), [
            'fitness_goal' => 'endurance',
            'experience_level' => 'intermediate',
            'training_days_per_week' => 3,
            'preferred_units' => 'lb',
            'weight' => 176.4,
            'height_cm' => null,
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->weight_kg)->toBe(80.0);
});

test('weight and height are optional', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('fitness.update'), [
            'fitness_goal' => 'general_health',
            'experience_level' => 'advanced',
            'training_days_per_week' => 5,
            'preferred_units' => 'kg',
        ])
        ->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->weight_kg)->toBeNull();
    expect($user->height_cm)->toBeNull();
});

test('fitness profile validation rejects invalid values', function (array $payload, string $errorField) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('fitness.edit'))
        ->patch(route('fitness.update'), [
            'fitness_goal' => 'lose_weight',
            'experience_level' => 'beginner',
            'training_days_per_week' => 4,
            'preferred_units' => 'kg',
            ...$payload,
        ])
        ->assertSessionHasErrors($errorField);
})->with([
    'invalid goal' => [['fitness_goal' => 'get_shredded'], 'fitness_goal'],
    'invalid level' => [['experience_level' => 'expert'], 'experience_level'],
    'too many days' => [['training_days_per_week' => 8], 'training_days_per_week'],
    'invalid units' => [['preferred_units' => 'st'], 'preferred_units'],
    'negative weight' => [['weight' => -5], 'weight'],
    'height too low' => [['height_cm' => 20], 'height_cm'],
]);
