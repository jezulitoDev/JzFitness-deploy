<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_session_exercise_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 8, 2)->default(0);
            $table->unsignedInteger('reps')->default(0);
            $table->unsignedInteger('duration')->nullable();
            $table->unsignedInteger('rest_seconds')->default(90);
            $table->decimal('rpe', 3, 1)->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();

            $table->index('gym_session_exercise_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_sets');
    }
};
