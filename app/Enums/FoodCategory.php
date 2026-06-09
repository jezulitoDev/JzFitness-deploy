<?php

namespace App\Enums;

enum FoodCategory: string
{
    case Fruit = 'fruit';
    case Vegetable = 'vegetable';
    case Meat = 'meat';
    case Fish = 'fish';
    case Dairy = 'dairy';
    case Egg = 'egg';
    case Grain = 'grain';
    case Legume = 'legume';
    case Nut = 'nut';
    case Beverage = 'beverage';
    case Snack = 'snack';
    case SauceOil = 'sauce_oil';
    case PreparedFood = 'prepared_food';
    case Supplement = 'supplement';

    public function label(): string
    {
        return match ($this) {
            self::Fruit => 'Frutas',
            self::Vegetable => 'Verduras y hortalizas',
            self::Meat => 'Carnes',
            self::Fish => 'Pescados y mariscos',
            self::Dairy => 'Lácteos',
            self::Egg => 'Huevos',
            self::Grain => 'Cereales y granos',
            self::Legume => 'Legumbres',
            self::Nut => 'Frutos secos y semillas',
            self::Beverage => 'Bebidas',
            self::Snack => 'Snacks y dulces',
            self::SauceOil => 'Salsas y aceites',
            self::PreparedFood => 'Comida preparada',
            self::Supplement => 'Suplementos',
        };
    }
}
