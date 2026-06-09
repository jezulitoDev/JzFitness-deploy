<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('summary')
            ->has('personalization'));
});

test('dashboard personalization reflects the fitness profile', function () {
    $user = User::factory()->withFitnessProfile()->create([
        'name' => 'Ana García',
        'fitness_goal' => 'gain_muscle',
        'experience_level' => 'intermediate',
        'training_days_per_week' => 4,
        'preferred_units' => 'kg',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('personalization.first_name', 'Ana')
            ->where('personalization.has_fitness_profile', true)
            ->where('personalization.goal_label', 'Ganar músculo')
            ->where('personalization.level_label', 'Intermedio')
            ->where('personalization.weekly_target', 4)
            ->where('personalization.units', 'kg'));
});

test('dashboard prompts users without a fitness profile to complete it', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('personalization.has_fitness_profile', false)
            ->where('personalization.weekly_target', null));
});