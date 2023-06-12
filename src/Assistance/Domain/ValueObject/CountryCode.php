<?php

namespace App\Assistance\Domain\ValueObject;

enum CountryCode: string
{
    case ARMENIA = 'AM';
    case GEORGIA = 'GE';
    case RUSSIA = 'RU';
    case TURKEY = 'TR';

    public static function any(): self
    {
        return self::cases()[array_rand(self::cases())];
    }
}
