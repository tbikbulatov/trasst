<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\CountryPolicy\Rule\DaysPerLast12MonthsRule;
use App\Assistance\Domain\ValueObject\CountryCode;

final class GeorgiaTaxResidencyPolicy extends AbstractTaxResidencyPolicy
{
    public function __construct()
    {
        $this->rules = [
            new DaysPerLast12MonthsRule(183),
        ];
    }

    public static function getCountryCode(): string
    {
        return CountryCode::GEORGIA->value;
    }
}
