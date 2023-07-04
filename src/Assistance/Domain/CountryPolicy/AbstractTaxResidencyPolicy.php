<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use OutOfRangeException;

abstract class AbstractTaxResidencyPolicy implements CountryTaxResidencyPolicyInterface
{
    /**
     * @var array<int,CountryTaxResidencyRuleInterface>
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

    public function current(): CountryTaxResidencyRuleInterface
    {
        return $this->valid() ? current($this->rules) : throw new OutOfRangeException();
    }

    public function next(): void
    {
        next($this->rules);
    }

    public function key(): ?int
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
