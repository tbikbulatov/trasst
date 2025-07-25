<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\CountryPolicy\Rule\DaysForLast12MonthsRule;
use App\Assistance\Domain\ValueObject\CountryCode;
use Override;

final class GeorgiaTaxResidencyPolicy extends AbstractTaxResidencyPolicy
{
    public function __construct()
    {
        $this->rules = [
            new DaysForLast12MonthsRule(183),
        ];
    }

    #[Override]
    public static function getCountryCode(): string
    {
        return CountryCode::GEORGIA->value;
    }
}
