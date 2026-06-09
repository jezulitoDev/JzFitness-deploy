<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workout_plan_day_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->date('scheduled_date');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_workouts');
    }
};
