<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\FoodLogController;
use App\Http\Controllers\GymSessionController;
use App\Http\Controllers\GymSetController;
use App\Http\Controllers\ScheduledWorkoutController;
use App\Http\Controllers\WorkoutPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::post('scheduled-workouts', [ScheduledWorkoutController::class, 'store'])
        ->name('scheduled-workouts.store');
    Route::patch('scheduled-workouts/{scheduled_workout}', [ScheduledWorkoutController::class, 'update'])
        ->name('scheduled-workouts.update');
    Route::delete('scheduled-workouts/{scheduled_workout}', [ScheduledWorkoutController::class, 'destroy'])
        ->name('scheduled-workouts.destroy');

    Route::resource('exercises', ExerciseController::class)->except(['show']);

    Route::resource('workout-plans', WorkoutPlanController::class);
    Route::post('workout-plans/{workout_plan}/duplicate', [WorkoutPlanController::class, 'duplicate'])
        ->name('workout-plans.duplicate');
    Route::patch('workout-plans/{workout_plan}/archive', [WorkoutPlanController::class, 'archive'])
        ->name('workout-plans.archive');
    Route::post('workout-plans/{workout_plan}/days', [WorkoutPlanController::class, 'storeDay'])
        ->name('workout-plans.days.store');
    Route::patch('workout-plans/{workout_plan}/days/reorder', [WorkoutPlanController::class, 'reorderDays'])
        ->name('workout-plans.days.reorder');
    Route::patch('workout-plans/{workout_plan}/days/{day}', [WorkoutPlanController::class, 'updateDay'])
        ->name('workout-plans.days.update');
    Route::delete('workout-plans/{workout_plan}/days/{day}', [WorkoutPlanController::class, 'destroyDay'])
        ->name('workout-plans.days.destroy');
    Route::post('workout-plans/{workout_plan}/days/{day}/exercises', [WorkoutPlanController::class, 'storeDayExercise'])
        ->name('workout-plans.days.exercises.store');
    Route::patch('workout-plans/{workout_plan}/days/{day}/exercises/reorder', [WorkoutPlanController::class, 'reorderDayExercises'])
        ->name('workout-plans.days.exercises.reorder');
    Route::patch('workout-plans/{workout_plan}/days/{day}/exercises/{dayExercise}', [WorkoutPlanController::class, 'updateDayExercise'])
        ->name('workout-plans.days.exercises.update');
    Route::delete('workout-plans/{workout_plan}/days/{day}/exercises/{dayExercise}', [WorkoutPlanController::class, 'destroyDayExercise'])
        ->name('workout-plans.days.exercises.destroy');

    Route::get('foods/search', [FoodController::class, 'search'])->name('foods.search');

    Route::get('nutrition', [FoodLogController::class, 'index'])->name('nutrition.index');
    Route::post('nutrition', [FoodLogController::class, 'store'])->name('nutrition.store');
    Route::patch('nutrition/{food_log}', [FoodLogController::class, 'update'])->name('nutrition.update');
    Route::delete('nutrition/{food_log}', [FoodLogController::class, 'destroy'])->name('nutrition.destroy');

    Route::get('gym-sessions', [GymSessionController::class, 'index'])->name('gym-sessions.index');
    Route::post('gym-sessions', [GymSessionController::class, 'store'])->name('gym-sessions.store');
    Route::get('gym-sessions/{gym_session}', [GymSessionController::class, 'show'])->name('gym-sessions.show');
    Route::get('gym-sessions/{gym_session}/play', [GymSessionController::class, 'play'])->name('gym-sessions.play');
    Route::patch('gym-sessions/{gym_session}/finish', [GymSessionController::class, 'finish'])->name('gym-sessions.finish');
    Route::post('gym-sessions/{gym_session}/exercises', [GymSessionController::class, 'storeExercise'])
        ->name('gym-sessions.exercises.store');

    Route::post('gym-session-exercises/{gym_session_exercise}/sets', [GymSetController::class, 'store'])
        ->name('gym-sets.store');
    Route::patch('gym-sets/{gym_set}', [GymSetController::class, 'update'])->name('gym-sets.update');
    Route::delete('gym-sets/{gym_set}', [GymSetController::class, 'destroy'])->name('gym-sets.destroy');
    Route::patch('gym-sets/{gym_set}/toggle', [GymSetController::class, 'toggle'])->name('gym-sets.toggle');
});
