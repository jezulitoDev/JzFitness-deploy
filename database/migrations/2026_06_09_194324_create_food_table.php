<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category');
            $table->unsignedSmallInteger('calories_per_100g');
            $table->decimal('protein_per_100g', 5, 1);
            $table->decimal('carbs_per_100g', 5, 1);
            $table->decimal('fat_per_100g', 5, 1);
            $table->decimal('serving_size_g', 6, 1)->nullable();
            $table->string('serving_label')->nullable();
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food');
    }
};
