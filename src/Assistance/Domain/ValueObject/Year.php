<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use App\Shared\Domain\Exception\ValidationException;

final readonly class Year
{
    private const MIN_YEAR = 1970;

    /**
     * @throws ValidationException
     */
    public function __construct(
        private int $year,
    ) {
        if ($year < self::MIN_YEAR) {
            throw new ValidationException('Year value can not be less than ' . self::MIN_YEAR);
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
