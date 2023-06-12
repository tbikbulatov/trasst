<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;

final /*readonly*/ class AnalysisOutcome implements ArrayAccess, Countable, Iterator
{
    /**
     * @var array<CountryOutcome>
     */
    private array $countryOutcomes;

    /**
     * @param array<CountryOutcome> $countryOutcomes
     */
    public function __construct(array $countryOutcomes)
    {
        $this->ensureTypes($countryOutcomes);

        $this->countryOutcomes = $countryOutcomes;
    }

    public function add(CountryOutcome $countryOutcome): self
    {
        return new static(array_merge($this->countryOutcomes, [$countryOutcome]));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function ensureTypes(array $countryOutcomes): void
    {
        array_walk($countryOutcomes, fn($v) => $v instanceof CountryOutcome ?: throw new InvalidArgumentException());
    }

    public function count(): int
    {
        return count($this->countryOutcomes);
    }

    public function current(): false|CountryOutcome
    {
        return current($this->countryOutcomes);
    }

    public function next(): void
    {
        next($this->countryOutcomes);
    }

    public function key(): int|null
    {
        return key($this->countryOutcomes);
    }

    public function valid(): bool
    {
        return (bool) current($this->countryOutcomes);
    }

    public function rewind(): void
    {
        reset($this->countryOutcomes);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->countryOutcomes[$offset]);
    }

    public function offsetGet(mixed $offset): CountryOutcome
    {
        return $this->countryOutcomes[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->countryOutcomes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->countryOutcomes[$offset]);
    }
}
