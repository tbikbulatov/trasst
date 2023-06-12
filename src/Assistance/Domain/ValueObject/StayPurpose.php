<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

enum StayPurpose: string
{
    case CIVIL_SERVICE = 'civil-service';
    case EDUCATION = 'education';
    case HOME_COUNTRY = 'home-country';
    case TOURISM = 'tourism';
    case MEDICAL_TREATMENT = 'treatment';

    public static function any(): self
    {
        return self::cases()[array_rand(self::cases())];
    }
}
