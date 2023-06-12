<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

/**
 * WIP
 * @link https://www.irs.gov/individuals/international-taxpayers/substantial-presence-test
 */
final class UnitedStatesTaxResidencyPolicy //extends AbstractTaxResidencyPolicy
{
    public function __construct()
    {
        $this->rules = [];
    }

    public static function getCountryCode(): string
    {
        return '';//CountryCode::UNITED_STATES->value;
    }
}
