<?php

namespace App\Enums;

enum MealType: string
{
    case Breakfast = 'breakfast';
    case Lunch = 'lunch';
    case Dinner = 'dinner';
    case Snack = 'snack';

    public function label(): string
    {
        return match ($this) {
            self::Breakfast => 'Desayuno',
            self::Lunch => 'Comida',
            self::Dinner => 'Cena',
            self::Snack => 'Snack',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Breakfast => 0,
            self::Lunch => 1,
            self::Snack => 2,
            self::Dinner => 3,
        };
    }
}
