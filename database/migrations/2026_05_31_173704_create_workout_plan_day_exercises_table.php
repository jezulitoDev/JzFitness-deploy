<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_plan_day_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_plan_day_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('default_rest_seconds')->default(90);
            $table->timestamps();

            $table->unique(['workout_plan_day_id', 'exercise_id'], 'wpday_exercise_unique');
            $table->index(['workout_plan_day_id', 'position'], 'wpday_position_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_plan_day_exercises');
    }
};
