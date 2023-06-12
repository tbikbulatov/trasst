<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\ValueObject\CountryCode;

interface CountryTaxResidencyPoliciesRegistryInterface
{
    public function has(CountryCode $country): bool;

    public function get(CountryCode $country): CountryTaxResidencyPolicyInterface;
}
