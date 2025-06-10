<?php

declare(strict_types=1);

namespace App\Tests\Unit\Assistance\Domain\ValueObject;

use App\Assistance\Domain\CountryPolicy\AbstractTaxResidencyPolicy;
use App\Assistance\Domain\CountryPolicy\CountryTaxResidencyPolicyInterface;
use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use App\Assistance\Domain\ValueObject\CountryCode;
use Override;

final class CountryTaxPolicyMother
{
    /**
     * @param array<CountryTaxResidencyRuleInterface> $rules
     */
    public static function create(array $rules, ?CountryCode $countryCode = null): CountryTaxResidencyPolicyInterface
    {
        return new class($rules, $countryCode) extends AbstractTaxResidencyPolicy {
            private static CountryCode $countryCode;

            public function __construct(array $rules, ?CountryCode $countryCode = null)
            {
                $this->rules = $rules;
                self::$countryCode = $countryCode ?? CountryCode::any();
            }

            #[Override]
            public static function getCountryCode(): string
            {
                return self::$countryCode->value;
            }
        };
    }
}
