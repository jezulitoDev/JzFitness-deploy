<?php

use App\Enums\FoodCategory;
use App\Enums\MealType;
use App\Models\Food;
use App\Models\User;
use Database\Seeders\FoodSeeder;

test('guests cannot search the food catalog', function () {
    $this->get(route('foods.search'))->assertRedirect(route('login'));
});

test('users can search foods by name', function () {
    $user = User::factory()->create();

    Food::factory()->create(['name' => 'Pechuga de pollo a la plancha']);
    Food::factory()->create(['name' => 'Arroz blanco cocido']);

    $this->actingAs($user)
        ->getJson(route('foods.search', ['q' => 'pollo']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'foods')
        ->assertJsonPath('foods.0.name', 'Pechuga de pollo a la plancha')
        ->assertJsonStructure([
            'foods' => [
                '*' => [
                    'id',
                    'name',
                    'category',
                    'category_label',
                    'calories_per_100g',
                    'protein_per_100g',
                    'carbs_per_100g',
                    'fat_per_100g',
                    'serving_size_g',
                    'serving_label',
                ],
            ],
        ]);
});

test('the food search can filter by category', function () {
    $user = User::factory()->create();

    Food::factory()->category(FoodCategory::Fruit)->create(['name' => 'Manzana']);
    Food::factory()->category(FoodCategory::Meat)->create(['name' => 'Manzana asada con carne']);

    $this->actingAs($user)
        ->getJson(route('foods.search', ['q' => 'manzana', 'category' => FoodCategory::Fruit->value]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'foods')
        ->assertJsonPath('foods.0.category', FoodCategory::Fruit->value)
        ->assertJsonPath('foods.0.category_label', FoodCategory::Fruit->label());
});

test('the food search rejects an invalid category', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('foods.search', ['category' => 'not-a-category']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category');
});

test('the food search limits the number of results', function () {
    $user = User::factory()->create();

    Food::factory()->count(30)->create();

    $response = $this->actingAs($user)
        ->getJson(route('foods.search'))
        ->assertSuccessful();

    expect(count($response->json('foods')))->toBeLessThanOrEqual(25);
});

test('a food computes calories and macros for a given amount in grams', function () {
    $food = Food::factory()->create([
        'calories_per_100g' => 165,
        'protein_per_100g' => 31.0,
        'carbs_per_100g' => 0.0,
        'fat_per_100g' => 3.6,
    ]);

    expect($food->macrosForGrams(150))->toBe([
        'calories' => 248,
        'protein_g' => 46.5,
        'carbs_g' => 0.0,
        'fat_g' => 5.4,
    ]);
});

test('users can log a meal from the catalog with automatically computed macros', function () {
    $user = User::factory()->create();

    Food::factory()->category(FoodCategory::Meat)->create([
        'name' => 'Pechuga de pollo a la plancha',
        'calories_per_100g' => 165,
        'protein_per_100g' => 31.0,
        'carbs_per_100g' => 0.0,
        'fat_per_100g' => 3.6,
    ]);

    $food = $this->actingAs($user)
        ->getJson(route('foods.search', ['q' => 'pechuga']))
        ->assertSuccessful()
        ->json('foods.0');

    $grams = 180;
    $factor = $grams / 100;

    $this->actingAs($user)
        ->post(route('nutrition.store'), [
            'consumed_on' => now()->toDateString(),
            'meal_type' => MealType::Lunch->value,
            'name' => $food['name'],
            'quantity' => "{$grams} g",
            'calories' => (int) round($food['calories_per_100g'] * $factor),
            'protein_g' => round($food['protein_per_100g'] * $factor, 1),
            'carbs_g' => round($food['carbs_per_100g'] * $factor, 1),
            'fat_g' => round($food['fat_per_100g'] * $factor, 1),
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $entry = $user->foodLogs()->where('name', 'Pechuga de pollo a la plancha')->first();

    expect($entry)->not->toBeNull()
        ->and($entry->calories)->toBe(297)
        ->and($entry->protein_g)->toBe(55.8)
        ->and($entry->carbs_g)->toBe(0.0)
        ->and($entry->fat_g)->toBe(6.5)
        ->and($entry->quantity)->toBe('180 g');
});

test('the nutrition diary shares the food categories for the catalog filter', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('nutrition.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('nutrition/index')
            ->has('foodCategories', count(FoodCategory::cases())));
});

test('the food seeder populates a complete catalog covering every category', function () {
    $this->seed(FoodSeeder::class);

    expect(Food::count())->toBeGreaterThanOrEqual(250);

    $seededCategories = Food::query()
        ->distinct()
        ->pluck('category')
        ->map(fn (FoodCategory $category): string => $category->value)
        ->all();

    foreach (FoodCategory::cases() as $category) {
        expect($seededCategories)->toContain($category->value);
    }
});

test('the food seeder is idempotent', function () {
    $this->seed(FoodSeeder::class);
    $count = Food::count();

    $this->seed(FoodSeeder::class);

    expect(Food::count())->toBe($count);
});
