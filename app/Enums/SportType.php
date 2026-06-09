<?php

namespace App\Enums;

enum SportType: string
{
    case Run = 'Run';
    case Ride = 'Ride';
    case Walk = 'Walk';
    case Hike = 'Hike';
    case Swim = 'Swim';
    case Workout = 'Workout';
    case Other = 'Other';

    public function label(): string
    {
        return match ($this) {
            self::Run => 'Running',
            self::Ride => 'Cycling',
            self::Walk => 'Walking',
            self::Hike => 'Hiking',
            self::Swim => 'Swimming',
            self::Workout => 'Workout',
            self::Other => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Run => 'running',
            self::Ride => 'cycling',
            self::Walk => 'walking',
            self::Hike => 'hiking',
            self::Swim => 'swimming',
            self::Workout => 'gym',
            self::Other => 'other',
        };
    }

    public static function tryFromStrava(?string $sportType): self
    {
        return self::tryFrom($sportType ?? '') ?? self::Other;
    }
}
