<?php

declare(strict_types=1);

namespace App\Assistance\Domain\Entity;

use App\Assistance\Domain\Exception\JournalHaveNoStaysException;
use App\Assistance\Domain\Exception\JournalStaysDatesOverlapsException;
use App\Assistance\Domain\Exception\StaysDatesOverlapsException;
use App\Assistance\Domain\Traits\StayDatesOverlappingValidationTrait;
use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\JournalId;
use App\Assistance\Domain\ValueObject\Stay;
use Countable;
use InvalidArgumentException;
use Iterator;
use OutOfRangeException;

/**
 * @template-implements Iterator<int,Stay>
 */
class Journal implements Countable, Iterator
{
    use StayDatesOverlappingValidationTrait;

    private readonly JournalId $id;

    /**
     * @var array<int,Stay>
     */
    private array $stays;

    /**
     * @param array<int,Stay> $stays
     *
     * @throws InvalidArgumentException
     * @throws JournalHaveNoStaysException
     * @throws JournalStaysDatesOverlapsException
     */
    public function __construct(JournalId $id, array $stays)
    {
        $this->ensureTypes($stays);

        $this->sort($stays);
        $this->validate($stays);

        $this->id = $id;
        $this->stays = $stays;
    }

    public function getId(): JournalId
    {
        return $this->id;
    }

    /**
     * @return array<int,Stay>
     */
    public function getStays(): array
    {
        return $this->stays;
    }

    /**
     * @return array<CountryJournal>
     */
    public function splitByCountries(): array
    {
        /** @var array<CountryJournal> $staysByCountry */
        $staysByCountry = [];

        foreach ($this->stays as $stay) {
            if (!isset($staysByCountry[$stay->country->value])) {
                $staysByCountry[$stay->country->value] = new CountryJournal([$stay]);
                continue;
            }

            $staysByCountry[$stay->country->value] = $staysByCountry[$stay->country->value]->add($stay);
        }

        return $staysByCountry;
    }

    public function count(): int
    {
        return count($this->stays);
    }

    public function current(): Stay
    {
        return $this->valid() ? current($this->stays) : throw new OutOfRangeException();
    }

    public function next(): void
    {
        next($this->stays);
    }

    public function key(): ?int
    {
        return key($this->stays);
    }

    public function valid(): bool
    {
        return (bool) current($this->stays);
    }

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
        array_walk($stays, fn (mixed $value) => assert($value instanceof Stay));
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
     * @throws JournalHaveNoStaysException
     * @throws JournalStaysDatesOverlapsException
     */
    private function validate(array $stays): void
    {
        if (empty($stays)) {
            throw new JournalHaveNoStaysException();
        }

        try {
            $this->validateStayDatesOverlapping($stays);
        } catch (StaysDatesOverlapsException $e) {
            throw JournalStaysDatesOverlapsException::fromPrevious($e);
        }
    }
}
