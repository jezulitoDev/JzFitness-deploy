<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strava_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('strava_activity_id');
            $table->string('name');
            $table->string('sport_type');
            $table->decimal('distance', 12, 2)->default(0);
            $table->unsignedInteger('moving_time')->default(0);
            $table->unsignedInteger('elapsed_time')->default(0);
            $table->decimal('elevation_gain', 8, 2)->default(0);
            $table->dateTime('started_at');
            $table->json('raw_json')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'strava_activity_id'], 'strava_activity_user_unique');
            $table->index(['user_id', 'started_at'], 'strava_activity_started_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strava_activities');
    }
};
