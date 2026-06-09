<?php

namespace App\Http\Controllers;

use App\Enums\FoodCategory;
use App\Enums\MealType;
use App\Http\Requests\StoreFoodLogRequest;
use App\Http\Requests\UpdateFoodLogRequest;
use App\Models\FoodLog;
use App\Services\NutritionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class FoodLogController extends Controller
{
    public function index(Request $request, NutritionService $nutrition): Response
    {
        $date = Carbon::parse($request->string('date', now()->toDateString())->toString());
        $user = $request->user();

        $daySummary = $nutrition->daySummary($user, $date);

        return Inertia::render('nutrition/index', [
            'date' => $date->toDateString(),
            'meals' => $daySummary['meals'],
            'totals' => $daySummary['totals'],
            'calorieTarget' => $nutrition->dailyCalorieTarget($user),
            'week' => $nutrition->weekSummary($user, $date),
            'mealTypes' => collect(MealType::cases())->map(fn (MealType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
            'foodCategories' => collect(FoodCategory::cases())->map(fn (FoodCategory $category): array => [
                'value' => $category->value,
                'label' => $category->label(),
            ]),
            'hasFitnessProfile' => $user->hasFitnessProfile(),
        ]);
    }

    public function store(StoreFoodLogRequest $request): RedirectResponse
    {
        $request->user()->foodLogs()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Food entry added.')]);

        return back();
    }

    public function update(UpdateFoodLogRequest $request, FoodLog $foodLog): RedirectResponse
    {
        $this->authorizeFoodLog($request, $foodLog);

        $foodLog->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Food entry updated.')]);

        return back();
    }

    public function destroy(Request $request, FoodLog $foodLog): RedirectResponse
    {
        $this->authorizeFoodLog($request, $foodLog);

        $foodLog->delete();

        return back();
    }

    protected function authorizeFoodLog(Request $request, FoodLog $foodLog): void
    {
        if ($foodLog->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
