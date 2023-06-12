<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;

abstract class AbstractTaxResidencyPolicy implements CountryTaxResidencyPolicyInterface
{
    /**
     * @var array<CountryTaxResidencyRuleInterface> $rules
     */
    protected array $rules;

    /**
     * @return non-empty-string Value of CountryCode enum
     */
    abstract public static function getCountryCode(): string;

    public function getRules(): array
    {
        return $this->rules;
    }

    public function current(): false|CountryTaxResidencyRuleInterface
    {
        return current($this->rules);
    }

    public function next(): void
    {
        next($this->rules);
    }

    public function key(): int|null
    {
        return key($this->rules);
    }

    public function valid(): bool
    {
        return (bool) current($this->rules);
    }

    public function rewind(): void
    {
        reset($this->rules);
    }
}
