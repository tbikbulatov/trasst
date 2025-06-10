<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\CountryPolicy\Rule\AndRule;
use App\Assistance\Domain\CountryPolicy\Rule\DaysForCalendarYearRule;
use App\Assistance\Domain\CountryPolicy\Rule\DaysForLastSequentialYearsInProportionRule;
use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\DaysOfYearRatio;
use Override;

/**
 * @see https://www.irs.gov/individuals/international-taxpayers/substantial-presence-test
 *
 * Quote from the document:
 *
 * You will be considered a United States resident for tax purposes
 * if you meet the substantial presence test for the calendar year.
 * To meet this test, you must be physically present in the United States (U.S.) on at least:
 * 1. 31 days during the current year, and
 * 2. 183 days during the 3-year period that includes the current year and the 2 years immediately before that, counting:
 *   - All the days you were present in the current year, and
 *   - 1/3 of the days you were present in the first year before the current year, and
 *   - 1/6 of the days you were present in the second year before the current year.
 */
final class UnitedStatesTaxResidencyPolicy extends AbstractTaxResidencyPolicy
{
    public function __construct()
    {
        $this->rules = [
            new AndRule(
                new DaysForCalendarYearRule(31),
                new DaysForLastSequentialYearsInProportionRule(
                    183,
                    new DaysOfYearRatio(1),
                    new DaysOfYearRatio(1, 3),
                    new DaysOfYearRatio(1, 6),
                ),
            ),
        ];
    }

    #[Override]
    public static function getCountryCode(): string
    {
        return CountryCode::UNITED_STATES->value;
    }
}
