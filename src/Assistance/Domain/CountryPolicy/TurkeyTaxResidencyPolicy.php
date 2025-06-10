<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\CountryPolicy\Rule\DaysForCalendarYearRule;
use App\Assistance\Domain\ValueObject\CountryCode;
use Override;

final class TurkeyTaxResidencyPolicy extends AbstractTaxResidencyPolicy
{
    public function __construct()
    {
        $this->rules = [
            new DaysForCalendarYearRule(183),
        ];
    }

    #[Override]
    public static function getCountryCode(): string
    {
        return CountryCode::TURKEY->value;
    }
}
