<?php

declare(strict_types=1);

namespace App\Assistance\Domain\Exception;

use App\Shared\Domain\Exception\ValidationException;
use DateTimeInterface;

class StaysDatesOverlapsException extends ValidationException
{
    public static function withDates(DateTimeInterface $date1, DateTimeInterface $date2): static
    {
        return new static(sprintf(
            'Stays dates within interval from %s to %s are overlapping',
            $date1->format('Y-m-d'),
            $date2->format('Y-m-d'),
        ));
    }
}
