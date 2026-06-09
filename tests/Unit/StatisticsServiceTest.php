<?php

use App\Models\GymSession;
use App\Models\StravaActivity;
use App\Models\GymSessionExercise;
use App\Models\GymSet;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\User;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('weekly volume sums completed sets weight times reps', function () {
    $user = User::factory()->create();
    $session = GymSession::factory()->for($user)->create([
        'started_at' => now(),
        'ended_at' => now()->addHour(),
    ]);
    $group = MuscleGroup::factory()->create();
    $exercise = Exercise::factory()->for($group, 'muscleGroup')->create();
    $sessionExercise = GymSessionExercise::factory()
        ->for($session, 'gymSession')
        ->for($exercise)
        ->create();
    GymSet::factory()->for($sessionExercise, 'gymSessionExercise')->completed()->create([
        'weight' => 100,
        'reps' => 8,
    ]);
    GymSet::factory()->for($sessionExercise, 'gymSessionExercise')->create([
        'weight' => 50,
        'reps' => 10,
        'completed' => false,
    ]);

    $volume = app(StatisticsService::class)->weeklyVolume($user);

    expect($volume)->toBe(800.0);
});

test('active streak days merges strava and gym dates without error', function () {
    $user = User::factory()->create();
    StravaActivity::factory()->for($user)->create([
        'started_at' => now(),
    ]);

    $streak = app(StatisticsService::class)->activeStreakDays($user);

    expect($streak)->toBeGreaterThanOrEqual(1);
});

test('dashboard summary returns expected keys', function () {
    $user = User::factory()->create();
    $summary = app(StatisticsService::class)->weeklySummary($user);

    expect($summary)->toHaveKeys([
        'gym_sessions',
        'strava_runs',
        'strava_rides',
        'strava_walks',
        'weekly_volume',
        'training_time_minutes',
        'active_streak_days',
    ]);
});
