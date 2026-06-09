<?php

use App\Enums\FitnessGoal;
use App\Enums\MealType;
use App\Models\FoodLog;
use App\Models\User;
use App\Services\NutritionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('calorie target uses mifflin st jeor with profile data', function () {
    $user = User::factory()->make([
        'weight_kg' => 80,
        'height_cm' => 180,
        'training_days_per_week' => 4,
        'fitness_goal' => null,
    ]);

    $result = app(NutritionService::class)->dailyCalorieTarget($user);

    // BMR = 10*80 + 6.25*180 - 5*30 - 78 = 1697; TDEE = 1697 * 1.55 = 2630.35
    expect($result['bmr'])->toBe(1697)
        ->and($result['tdee'])->toBe(2630)
        ->and($result['target'])->toBe(2650);
});

test('calorie target falls back to sensible defaults without profile data', function () {
    $user = User::factory()->make([
        'weight_kg' => null,
        'height_cm' => null,
        'training_days_per_week' => null,
        'fitness_goal' => null,
    ]);

    $result = app(NutritionService::class)->dailyCalorieTarget($user);

    // BMR = 10*75 + 6.25*170 - 150 - 78 = 1584.5; TDEE = 1584.5 * 1.375 = 2178.7
    expect($result['bmr'])->toBe(1585)
        ->and($result['target'])->toBe(2200);
});

test('losing weight applies a deficit and gaining muscle a surplus', function () {
    $base = [
        'weight_kg' => 80,
        'height_cm' => 180,
        'training_days_per_week' => 4,
    ];

    $service = app(NutritionService::class);

    $maintain = $service->dailyCalorieTarget(User::factory()->make($base + ['fitness_goal' => FitnessGoal::GeneralHealth]));
    $deficit = $service->dailyCalorieTarget(User::factory()->make($base + ['fitness_goal' => FitnessGoal::LoseWeight]));
    $surplus = $service->dailyCalorieTarget(User::factory()->make($base + ['fitness_goal' => FitnessGoal::GainMuscle]));

    expect($deficit['target'])->toBeLessThan($maintain['target'])
        ->and($surplus['target'])->toBeGreaterThan($maintain['target']);
});

test('day summary groups entries by meal and sums totals', function () {
    $user = User::factory()->create();

    FoodLog::factory()->for($user)->create([
        'consumed_on' => '2026-06-09',
        'meal_type' => MealType::Breakfast,
        'calories' => 400,
        'protein_g' => 20,
        'carbs_g' => 50,
        'fat_g' => 10,
    ]);
    FoodLog::factory()->for($user)->create([
        'consumed_on' => '2026-06-09',
        'meal_type' => MealType::Dinner,
        'calories' => 600,
        'protein_g' => 40,
        'carbs_g' => 30,
        'fat_g' => 25,
    ]);

    $summary = app(NutritionService::class)->daySummary($user, Carbon::parse('2026-06-09'));

    $breakfast = collect($summary['meals'])->firstWhere('meal_type', 'breakfast');
    $dinner = collect($summary['meals'])->firstWhere('meal_type', 'dinner');

    expect($summary['meals'])->toHaveCount(4)
        ->and($breakfast['calories'])->toBe(400)
        ->and($dinner['calories'])->toBe(600)
        ->and($summary['totals']['calories'])->toBe(1000)
        ->and($summary['totals']['protein_g'])->toBe(60.0)
        ->and($summary['totals']['carbs_g'])->toBe(80.0)
        ->and($summary['totals']['fat_g'])->toBe(35.0);
});
