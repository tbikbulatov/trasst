<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use ArrayAccess;
use Countable;
use Iterator;
use OutOfRangeException;

/**
 * @template-implements ArrayAccess<int,CountryOutcome>
 * @template-implements Iterator<int,CountryOutcome>
 */
final /* readonly */ class AnalysisOutcome implements ArrayAccess, Countable, Iterator
{
    /**
     * @var array<int,CountryOutcome>
     */
    private array $countryOutcomes;

    /**
     * @param array<int,CountryOutcome> $countryOutcomes
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
     * @param array<int,CountryOutcome> $countryOutcomes
     */
    private function ensureTypes(array $countryOutcomes): void
    {
        array_walk($countryOutcomes, fn (mixed $v) => assert($v instanceof CountryOutcome));
    }

    public function count(): int
    {
        return count($this->countryOutcomes);
    }

    public function current(): CountryOutcome
    {
        return $this->valid() ? current($this->countryOutcomes) : throw new OutOfRangeException();
    }

    public function next(): void
    {
        next($this->countryOutcomes);
    }

    public function key(): ?int
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

    /**
     * @param int            $offset
     * @param CountryOutcome $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->countryOutcomes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->countryOutcomes[$offset]);
    }
}
