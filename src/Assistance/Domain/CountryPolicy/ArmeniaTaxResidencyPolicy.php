<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\CountryPolicy\Rule\DaysPerCalendarYearRule;
use App\Assistance\Domain\ValueObject\CountryCode;

final class ArmeniaTaxResidencyPolicy extends AbstractTaxResidencyPolicy
{
    public function __construct()
    {
        $this->rules = [
            new DaysPerCalendarYearRule(183),
        ];
    }

    public static function getCountryCode(): string
    {
        return CountryCode::ARMENIA->value;
    }
}
