<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_session_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('default_rest_seconds')->default(90);
            $table->timestamps();

            $table->index(['gym_session_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_session_exercises');
    }
};
