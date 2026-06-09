<?php

namespace Database\Factories;

use App\Models\StravaActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StravaActivity>
 */
class StravaActivityFactory extends Factory
{
    protected $model = StravaActivity::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'strava_activity_id' => fake()->unique()->numberBetween(100000, 999999999),
            'name' => fake()->words(3, true),
            'sport_type' => fake()->randomElement(['Run', 'Ride', 'Walk', 'Hike', 'Swim']),
            'distance' => fake()->randomFloat(2, 1000, 50000),
            'moving_time' => fake()->numberBetween(600, 7200),
            'elapsed_time' => fake()->numberBetween(600, 7500),
            'elevation_gain' => fake()->randomFloat(2, 0, 500),
            'started_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'raw_json' => [],
        ];
    }
}
