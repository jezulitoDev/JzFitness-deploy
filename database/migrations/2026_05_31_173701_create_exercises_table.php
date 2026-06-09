<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muscle_group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('equipment')->nullable();
            $table->text('description')->nullable();
            $table->string('video_url')->nullable();
            $table->timestamps();

            $table->index(['muscle_group_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
