<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Year
{
    private const MIN_YEAR = 1970;

    public function __construct(
        private int $year,
    ) {
        if ($year < self::MIN_YEAR) {
            throw new InvalidArgumentException('Value can not be less than ' . self::MIN_YEAR);
        }
    }

    public static function fromInt(int $year): self
    {
        return new self($year);
    }

    public function toInt(): int
    {
        return $this->year;
    }

    public function equals(self $year): bool
    {
        return $this->year === $year->year;
    }
}
