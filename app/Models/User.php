<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\ExperienceLevel;
use App\Enums\FitnessGoal;
use App\Enums\WeightUnit;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'fitness_goal', 'experience_level', 'training_days_per_week', 'weight_kg', 'height_cm', 'preferred_units'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'fitness_goal' => FitnessGoal::class,
            'experience_level' => ExperienceLevel::class,
            'training_days_per_week' => 'integer',
            'weight_kg' => 'float',
            'height_cm' => 'integer',
            'preferred_units' => WeightUnit::class,
        ];
    }

    public function hasFitnessProfile(): bool
    {
        return $this->fitness_goal !== null && $this->experience_level !== null;
    }

    public function stravaAccount(): HasOne
    {
        return $this->hasOne(StravaAccount::class);
    }

    public function stravaActivities(): HasMany
    {
        return $this->hasMany(StravaActivity::class);
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function gymSessions(): HasMany
    {
        return $this->hasMany(GymSession::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    public function scheduledWorkouts(): HasMany
    {
        return $this->hasMany(ScheduledWorkout::class);
    }

    public function foodLogs(): HasMany
    {
        return $this->hasMany(FoodLog::class);
    }
}
