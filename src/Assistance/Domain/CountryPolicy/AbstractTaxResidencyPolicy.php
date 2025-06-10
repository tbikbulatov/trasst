<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy;

use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use OutOfBoundsException;
use OutOfRangeException;
use Override;

abstract class AbstractTaxResidencyPolicy implements CountryTaxResidencyPolicyInterface
{
    /**
     * @var array<int,CountryTaxResidencyRuleInterface>
     */
    protected array $rules;

    /**
     * @return non-empty-string Value of CountryCode enum
     */
    #[Override]
    abstract public static function getCountryCode(): string;

    #[Override]
    public function current(): CountryTaxResidencyRuleInterface
    {
        if ($this->valid() && ($current = current($this->rules))) {
            return $current;
        }

        throw new OutOfRangeException();
    }

    #[Override]
    public function next(): void
    {
        next($this->rules);
    }

    #[Override]
    public function key(): int
    {
        return key($this->rules) ?? throw new OutOfBoundsException();
    }

    #[Override]
    public function valid(): bool
    {
        return (bool) current($this->rules);
    }

    #[Override]
    public function rewind(): void
    {
        reset($this->rules);
    }
}
