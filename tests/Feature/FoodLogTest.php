<?php

use App\Enums\MealType;
use App\Models\FoodLog;
use App\Models\User;

test('guests cannot access the nutrition diary', function () {
    $this->get(route('nutrition.index'))->assertRedirect(route('login'));
});

test('users can see their daily diary with totals and calorie target', function () {
    $user = User::factory()->create([
        'weight_kg' => 80,
        'height_cm' => 180,
        'training_days_per_week' => 4,
    ]);

    FoodLog::factory()->for($user)->on(now()->toDateString())->meal(MealType::Breakfast)->create([
        'calories' => 400,
        'protein_g' => 20,
    ]);
    FoodLog::factory()->for($user)->on(now()->toDateString())->meal(MealType::Lunch)->create([
        'calories' => 600,
        'protein_g' => 40,
    ]);

    $this->actingAs($user)
        ->get(route('nutrition.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('nutrition/index')
            ->where('totals.calories', 1000)
            ->where('totals.protein_g', 60)
            ->has('meals', 4)
            ->has('calorieTarget.target')
            ->has('week', 7));
});

test('the diary only shows entries for the requested date', function () {
    $user = User::factory()->create();

    FoodLog::factory()->for($user)->on(now()->toDateString())->create(['calories' => 500]);
    FoodLog::factory()->for($user)->on(now()->subDay()->toDateString())->create(['calories' => 300]);

    $this->actingAs($user)
        ->get(route('nutrition.index', ['date' => now()->subDay()->toDateString()]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('totals.calories', 300)
            ->where('date', now()->subDay()->toDateString()));
});

test('users can add a food entry', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('nutrition.store'), [
            'consumed_on' => now()->toDateString(),
            'meal_type' => MealType::Breakfast->value,
            'name' => 'Avena con plátano',
            'quantity' => '80 g',
            'calories' => 390,
            'protein_g' => 12,
            'carbs_g' => 70,
            'fat_g' => 6,
        ])
        ->assertRedirect();

    expect($user->foodLogs()->where('name', 'Avena con plátano')->where('calories', 390)->exists())->toBeTrue();
});

test('food entries require valid data', function (array $payload, string $errorField) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('nutrition.store'), array_merge([
            'consumed_on' => now()->toDateString(),
            'meal_type' => MealType::Lunch->value,
            'name' => 'Arroz',
            'calories' => 200,
        ], $payload))
        ->assertSessionHasErrors($errorField);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'missing calories' => [['calories' => null], 'calories'],
    'negative calories' => [['calories' => -10], 'calories'],
    'invalid meal type' => [['meal_type' => 'brunch'], 'meal_type'],
    'invalid date' => [['consumed_on' => 'not-a-date'], 'consumed_on'],
]);

test('users can update their food entry', function () {
    $user = User::factory()->create();
    $entry = FoodLog::factory()->for($user)->create(['calories' => 100]);

    $this->actingAs($user)
        ->patch(route('nutrition.update', $entry), [
            'calories' => 250,
            'name' => 'Pollo con arroz',
        ])
        ->assertRedirect();

    $entry->refresh();

    expect($entry->calories)->toBe(250)
        ->and($entry->name)->toBe('Pollo con arroz');
});

test('users can delete their food entry', function () {
    $user = User::factory()->create();
    $entry = FoodLog::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('nutrition.destroy', $entry))
        ->assertRedirect();

    $this->assertModelMissing($entry);
});

test('users cannot manage food entries of other users', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $entry = FoodLog::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->patch(route('nutrition.update', $entry), ['calories' => 1])
        ->assertForbidden();

    $this->actingAs($intruder)
        ->delete(route('nutrition.destroy', $entry))
        ->assertForbidden();
});

test('the weekly summary aggregates calories per day', function () {
    $user = User::factory()->create();
    $monday = now()->startOfWeek();

    FoodLog::factory()->for($user)->on($monday->toDateString())->create(['calories' => 500]);
    FoodLog::factory()->for($user)->on($monday->toDateString())->create(['calories' => 300]);
    FoodLog::factory()->for($user)->on($monday->copy()->addDay()->toDateString())->create(['calories' => 700]);

    $this->actingAs($user)
        ->get(route('nutrition.index', ['date' => $monday->toDateString()]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('week.0.calories', 800)
            ->where('week.1.calories', 700)
            ->where('week.2.calories', 0));
});

test('the dashboard shows todays calories against the target', function () {
    $user = User::factory()->create();

    FoodLog::factory()->for($user)->on(now()->toDateString())->create(['calories' => 450]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('nutrition.calories_today', 450)
            ->has('nutrition.calorie_target'));
});
