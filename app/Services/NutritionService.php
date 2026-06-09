<?php

namespace App\Services;

use App\Enums\FitnessGoal;
use App\Enums\MealType;
use App\Models\FoodLog;
use App\Models\User;
use Illuminate\Support\Carbon;

class NutritionService
{
    private const float DEFAULT_WEIGHT_KG = 75.0;

    private const int DEFAULT_HEIGHT_CM = 170;

    private const int DEFAULT_AGE_YEARS = 30;

    /**
     * Midpoint between the Mifflin-St Jeor male (+5) and female (-161) constants,
     * used because the profile does not store sex.
     */
    private const int SEX_CONSTANT = -78;

    /**
     * Estimated daily calorie target based on the fitness profile (Mifflin-St Jeor).
     *
     * @return array{target: int, bmr: int, tdee: int, goal_label: string|null}
     */
    public function dailyCalorieTarget(User $user): array
    {
        $weight = $user->weight_kg ?? self::DEFAULT_WEIGHT_KG;
        $height = $user->height_cm ?? self::DEFAULT_HEIGHT_CM;

        $bmr = (10 * $weight) + (6.25 * $height) - (5 * self::DEFAULT_AGE_YEARS) + self::SEX_CONSTANT;
        $tdee = $bmr * $this->activityFactor($user);
        $target = $tdee * $this->goalMultiplier($user->fitness_goal);

        return [
            'target' => (int) (round($target / 50) * 50),
            'bmr' => (int) round($bmr),
            'tdee' => (int) round($tdee),
            'goal_label' => $user->fitness_goal?->label(),
        ];
    }

    /**
     * Entries and totals for a single day, grouped by meal type.
     *
     * @return array{meals: list<array<string, mixed>>, totals: array{calories: int, protein_g: float, carbs_g: float, fat_g: float}}
     */
    public function daySummary(User $user, Carbon $date): array
    {
        $entries = $user->foodLogs()
            ->whereDate('consumed_on', $date->toDateString())
            ->orderBy('created_at')
            ->get();

        $meals = [];

        foreach (MealType::cases() as $mealType) {
            $mealEntries = $entries->where('meal_type', $mealType)->values();

            $meals[] = [
                'meal_type' => $mealType->value,
                'label' => $mealType->label(),
                'sort_order' => $mealType->sortOrder(),
                'entries' => $mealEntries,
                'calories' => (int) $mealEntries->sum('calories'),
            ];
        }

        usort($meals, fn (array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);

        return [
            'meals' => $meals,
            'totals' => [
                'calories' => (int) $entries->sum('calories'),
                'protein_g' => round((float) $entries->sum('protein_g'), 1),
                'carbs_g' => round((float) $entries->sum('carbs_g'), 1),
                'fat_g' => round((float) $entries->sum('fat_g'), 1),
            ],
        ];
    }

    /**
     * Calories per day for the week containing the given date.
     *
     * @return list<array{date: string, calories: int}>
     */
    public function weekSummary(User $user, Carbon $date): array
    {
        $start = $date->copy()->startOfWeek();
        $end = $start->copy()->addDays(6);

        $caloriesByDay = $user->foodLogs()
            ->whereDate('consumed_on', '>=', $start->toDateString())
            ->whereDate('consumed_on', '<=', $end->toDateString())
            ->get(['consumed_on', 'calories'])
            ->groupBy(fn (FoodLog $log): string => $log->consumed_on->toDateString())
            ->map(fn ($logs): int => (int) $logs->sum('calories'));

        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i)->toDateString();

            $days[] = [
                'date' => $day,
                'calories' => (int) ($caloriesByDay[$day] ?? 0),
            ];
        }

        return $days;
    }

    protected function activityFactor(User $user): float
    {
        return match (true) {
            $user->training_days_per_week === null => 1.375,
            $user->training_days_per_week <= 1 => 1.2,
            $user->training_days_per_week <= 3 => 1.375,
            $user->training_days_per_week <= 5 => 1.55,
            default => 1.725,
        };
    }

    protected function goalMultiplier(?FitnessGoal $goal): float
    {
        return match ($goal) {
            FitnessGoal::LoseWeight => 0.85,
            FitnessGoal::GainMuscle => 1.10,
            default => 1.0,
        };
    }
}
