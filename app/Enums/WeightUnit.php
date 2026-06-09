<?php

namespace App\Enums;

enum WeightUnit: string
{
    case Kilograms = 'kg';
    case Pounds = 'lb';

    private const float KG_TO_LB = 2.20462;

    public function label(): string
    {
        return match ($this) {
            self::Kilograms => 'Kilogramos (kg)',
            self::Pounds => 'Libras (lb)',
        };
    }

    public function fromKilograms(float $kilograms): float
    {
        return match ($this) {
            self::Kilograms => round($kilograms, 1),
            self::Pounds => round($kilograms * self::KG_TO_LB, 1),
        };
    }

    public function toKilograms(float $value): float
    {
        return match ($this) {
            self::Kilograms => round($value, 1),
            self::Pounds => round($value / self::KG_TO_LB, 1),
        };
    }
}
