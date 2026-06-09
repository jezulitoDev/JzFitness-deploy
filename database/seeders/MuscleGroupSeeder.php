<?php

namespace Database\Seeders;

use App\Models\MuscleGroup;
use Illuminate\Database\Seeder;

class MuscleGroupSeeder extends Seeder
{
    /**
     * Group names kept exactly as stored in existing databases (no accents on the originals).
     *
     * @var list<string>
     */
    private array $groups = [
        'Pecho',
        'Espalda',
        'Piernas',
        'Hombros',
        'Biceps',
        'Triceps',
        'Core',
        'Antebrazos',
        'Cuádriceps',
        'Isquiotibiales',
        'Glúteos',
        'Gemelos',
        'Trapecio',
        'Lumbar',
        'Cuerpo completo',
        'Cardio',
    ];

    public function run(): void
    {
        foreach ($this->groups as $name) {
            MuscleGroup::query()->firstOrCreate(['name' => $name]);
        }
    }
}
