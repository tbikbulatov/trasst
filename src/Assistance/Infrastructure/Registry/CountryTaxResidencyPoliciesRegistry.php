<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\Registry;

use App\Assistance\Domain\CountryPolicy\CountryTaxResidencyPoliciesRegistryInterface;
use App\Assistance\Domain\CountryPolicy\CountryTaxResidencyPolicyInterface;
use App\Assistance\Domain\ValueObject\CountryCode;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class CountryTaxResidencyPoliciesRegistry implements CountryTaxResidencyPoliciesRegistryInterface
{
    /**
     * @var ServiceLocator<CountryTaxResidencyPolicyInterface>
     */
    private ServiceLocator $locator;

    /**
     * @param ServiceLocator<CountryTaxResidencyPolicyInterface> $locator
     */
    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    public function has(CountryCode $country): bool
    {
        return $this->locator->has($country->value);
    }

    public function get(CountryCode $country): CountryTaxResidencyPolicyInterface
    {
        return $this->locator->get($country->value);
    }
}
