<?php

declare(strict_types=1);

namespace App\Assistance\Domain\Exception;

use DateTimeInterface;
use RangeException;

class StayDatesOverlappingException extends RangeException
{
    public static function withDates(DateTimeInterface $date1, DateTimeInterface $date2): static
    {
        return new static(sprintf(
            'Dates %s and %s are overlapping', $date1->format('Y-m-d'), $date2->format('Y-m-d')
        ));
    }
}
