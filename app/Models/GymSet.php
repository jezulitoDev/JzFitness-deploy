<?php

namespace App\Models;

use Database\Factories\GymSetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['gym_session_exercise_id', 'weight', 'reps', 'duration', 'rest_seconds', 'rpe', 'completed'])]
class GymSet extends Model
{
    /** @use HasFactory<GymSetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'rpe' => 'decimal:1',
            'completed' => 'boolean',
        ];
    }

    public function gymSessionExercise(): BelongsTo
    {
        return $this->belongsTo(GymSessionExercise::class);
    }

    public function volume(): float
    {
        return (float) $this->weight * $this->reps;
    }
}
