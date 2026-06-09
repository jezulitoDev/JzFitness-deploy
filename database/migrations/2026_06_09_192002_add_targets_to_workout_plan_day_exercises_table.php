<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_plan_day_exercises', function (Blueprint $table) {
            $table->unsignedTinyInteger('target_sets')->nullable()->after('position');
            $table->unsignedSmallInteger('target_reps')->nullable()->after('target_sets');
            $table->decimal('target_weight', 6, 2)->nullable()->after('target_reps');
        });
    }

    public function down(): void
    {
        Schema::table('workout_plan_day_exercises', function (Blueprint $table) {
            $table->dropColumn(['target_sets', 'target_reps', 'target_weight']);
        });
    }
};
