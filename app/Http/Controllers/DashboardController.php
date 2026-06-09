<?php

namespace App\Http\Controllers;

use App\Enums\WeightUnit;
use App\Services\NutritionService;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, StatisticsService $statistics, NutritionService $nutrition): Response
    {
        $user = $request->user();
        $summary = $statistics->weeklySummary($user);
        $units = $user->preferred_units ?? WeightUnit::Kilograms;

        $workoutsThisWeek = $summary['gym_sessions']
            + $summary['strava_runs']
            + $summary['strava_rides']
            + $summary['strava_walks'];

        return Inertia::render('dashboard', [
            'summary' => $summary,
            'nutrition' => [
                'calories_today' => (int) $user->foodLogs()
                    ->whereDate('consumed_on', now()->toDateString())
                    ->sum('calories'),
                'calorie_target' => $nutrition->dailyCalorieTarget($user)['target'],
            ],
            'personalization' => [
                'first_name' => Str::of($user->name)->before(' ')->toString(),
                'has_fitness_profile' => $user->hasFitnessProfile(),
                'goal_label' => $user->fitness_goal?->label(),
                'goal_tagline' => $user->fitness_goal?->tagline(),
                'level_label' => $user->experience_level?->label(),
                'weekly_target' => $user->training_days_per_week,
                'workouts_this_week' => $workoutsThisWeek,
                'weekly_volume' => $units->fromKilograms($summary['weekly_volume']),
                'units' => $units->value,
            ],
        ]);
    }
}
