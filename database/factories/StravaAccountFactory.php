<?php

namespace Database\Factories;

use App\Models\StravaAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StravaAccount>
 */
class StravaAccountFactory extends Factory
{
    protected $model = StravaAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'strava_id' => fake()->unique()->numberBetween(100000, 999999999),
            'access_token' => fake()->sha256(),
            'refresh_token' => fake()->sha256(),
            'expires_at' => now()->addHours(6),
        ];
    }
}
