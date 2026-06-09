<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('fitness_goal')->nullable()->after('email_verified_at');
            $table->string('experience_level')->nullable()->after('fitness_goal');
            $table->unsignedTinyInteger('training_days_per_week')->nullable()->after('experience_level');
            $table->decimal('weight_kg', 5, 1)->nullable()->after('training_days_per_week');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('weight_kg');
            $table->string('preferred_units', 2)->default('kg')->after('height_cm');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'fitness_goal',
                'experience_level',
                'training_days_per_week',
                'weight_kg',
                'height_cm',
                'preferred_units',
            ]);
        });
    }
};
