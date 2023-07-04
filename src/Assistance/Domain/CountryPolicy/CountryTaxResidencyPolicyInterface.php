<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use Iterator;

/**
 * @extends Iterator<int,CountryTaxResidencyRuleInterface>
 */
interface CountryTaxResidencyPolicyInterface extends Iterator
{
    /**
     * @return non-empty-string Value of CountryCode enum
     */
    public static function getCountryCode(): string;

    /**
     * @return array<CountryTaxResidencyRuleInterface>
     */
    public function getRules(): array;

    public function current(): CountryTaxResidencyRuleInterface;
}
