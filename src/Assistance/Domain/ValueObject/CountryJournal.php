<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use App\Assistance\Domain\Exception\StaysDatesOverlapsException;
use App\Assistance\Domain\Traits\StayDatesOverlappingValidationTrait;
use App\Shared\Domain\Exception\ValidationException;
use Countable;
use InvalidArgumentException;
use Iterator;
use OutOfBoundsException;
use OutOfRangeException;
use Override;

/**
 * @template-implements Iterator<int,Stay>
 */
final class CountryJournal implements Countable, Iterator
{
    use StayDatesOverlappingValidationTrait;

    public readonly CountryCode $country;

    /**
     * @var array<int,Stay>
     */
    private array $stays;

    /**
     * @param array<int,Stay> $stays
     *
     * @throws InvalidArgumentException
     * @throws ValidationException
     * @throws StaysDatesOverlapsException
     */
    public function __construct(array $stays)
    {
        $this->ensureTypes($stays);

        $this->validate($stays);
        $this->sort($stays);

        $this->country = $stays[0]->country;
        $this->stays = $stays;
    }

    public function add(Stay $stay): self
    {
        return new self(array_merge($this->stays, [$stay]));
    }

    #[Override]
    public function count(): int
    {
        return count($this->stays);
    }

    #[Override]
    public function current(): Stay
    {
        if ($this->valid() && ($current = current($this->stays))) {
            return $current;
        }

        throw new OutOfRangeException();
    }

    #[Override]
    public function next(): void
    {
        next($this->stays);
    }

    #[Override]
    public function key(): int
    {
        return key($this->stays) ?? throw new OutOfBoundsException();
    }

    #[Override]
    public function valid(): bool
    {
        return (bool) current($this->stays);
    }

    #[Override]
    public function rewind(): void
    {
        reset($this->stays);
    }

    /**
     * @param array<int,Stay> $stays
     *
     * @throws InvalidArgumentException
     */
    private function ensureTypes(array $stays): void
    {
        array_walk($stays, fn (mixed $value) => $value instanceof Stay ?: throw new InvalidArgumentException());
    }

    /**
     * @param array<int,Stay> &$stays
     */
    private function sort(array &$stays): void
    {
        usort($stays, fn (Stay $a, Stay $b) => $a->dateFrom <=> $b->dateFrom);
    }

    /**
     * @param array<int,Stay> $stays
     *
     * @throws ValidationException
     * @throws StaysDatesOverlapsException
     */
    private function validate(array $stays): void
    {
        if (empty($stays)) {
            throw new ValidationException('Country journal must contain stays');
        }

        $countries = array_map(fn (Stay $s) => $s->country->value, $stays);
        if (count(array_unique($countries)) > 1) {
            throw new ValidationException('Country journal must contain only one country stays');
        }

        $this->validateStayDatesOverlapping($stays);
    }
}
