<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use OutOfRangeException;
use Override;

/**
 * @template-implements ArrayAccess<int, CountryOutcome>
 * @template-implements Iterator<int, CountryOutcome>
 */
final class AnalysisOutcome implements ArrayAccess, Countable, Iterator
{
    /**
     * @var array<int, CountryOutcome>
     */
    private array $countryOutcomes;

    private int $position;

    /**
     * @param array<int, CountryOutcome> $countryOutcomes
     */
    public function __construct(array $countryOutcomes)
    {
        $this->ensureTypes($countryOutcomes);

        $this->countryOutcomes = $countryOutcomes;
        $this->position = 0;
    }

    public function add(CountryOutcome $countryOutcome): self
    {
        return new static(array_merge($this->countryOutcomes, [$countryOutcome]));
    }

    /**
     * @param array<int,CountryOutcome> $countryOutcomes
     *
     * @throws InvalidArgumentException
     */
    private function ensureTypes(array $countryOutcomes): void
    {
        array_walk($countryOutcomes, fn (mixed $v) => $v instanceof CountryOutcome ?: throw new InvalidArgumentException());
    }

    #[Override]
    public function count(): int
    {
        return count($this->countryOutcomes);
    }

    #[Override]
    public function current(): CountryOutcome
    {
        if ($this->valid()) {
            return $this->countryOutcomes[$this->position];
        }

        throw new OutOfRangeException();
    }

    #[Override]
    public function next(): void
    {
        ++$this->position;
    }

    #[Override]
    public function key(): int
    {
        return $this->position;
    }

    #[Override]
    public function valid(): bool
    {
        return isset($this->countryOutcomes[$this->position]);
    }

    #[Override]
    public function rewind(): void
    {
        $this->position = 0;
    }

    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->countryOutcomes[$offset]);
    }

    #[Override]
    public function offsetGet(mixed $offset): CountryOutcome
    {
        return $this->countryOutcomes[$offset];
    }

    /**
     * @param int $offset
     * @param CountryOutcome $value
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->countryOutcomes[$offset] = $value;
    }

    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        unset($this->countryOutcomes[$offset]);
    }
}
