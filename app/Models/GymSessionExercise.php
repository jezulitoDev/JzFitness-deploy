<?php

namespace App\Models;

use Database\Factories\GymSessionExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['gym_session_id', 'exercise_id', 'order', 'default_rest_seconds'])]
class GymSessionExercise extends Model
{
    /** @use HasFactory<GymSessionExerciseFactory> */
    use HasFactory;

    public function gymSession(): BelongsTo
    {
        return $this->belongsTo(GymSession::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(GymSet::class);
    }
}
