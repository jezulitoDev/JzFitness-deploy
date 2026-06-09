<?php

namespace App\Enums;

enum FitnessGoal: string
{
    case LoseWeight = 'lose_weight';
    case GainMuscle = 'gain_muscle';
    case Endurance = 'endurance';
    case GeneralHealth = 'general_health';

    public function label(): string
    {
        return match ($this) {
            self::LoseWeight => 'Perder peso',
            self::GainMuscle => 'Ganar músculo',
            self::Endurance => 'Resistencia',
            self::GeneralHealth => 'Salud general',
        };
    }

    public function tagline(): string
    {
        return match ($this) {
            self::LoseWeight => 'Cada sesión te acerca a tu peso objetivo.',
            self::GainMuscle => 'La constancia y la sobrecarga progresiva construyen músculo.',
            self::Endurance => 'Suma minutos de calidad y tu resistencia crecerá.',
            self::GeneralHealth => 'Moverte cada día es la mejor inversión en tu salud.',
        };
    }
}
