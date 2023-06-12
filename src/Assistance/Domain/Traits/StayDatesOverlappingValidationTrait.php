<?php

declare(strict_types=1);

namespace App\Assistance\Domain\Traits;

use App\Assistance\Domain\Exception\StayDatesOverlappingException;
use App\Assistance\Domain\ValueObject\Stay;

trait StayDatesOverlappingValidationTrait
{
    /**
     * @param array<Stay> $stays
     * @throws StayDatesOverlappingException
     */
    private function validateStayDatesOverlapping(array $stays): void
    {
        foreach ($stays as $i => $stay) {
            $nextStay = $stays[$i + 1] ?? null;
            if (empty($nextStay)) {
                break;
            }

            $daysDiff = (int)$stay->dateTo->diff($nextStay->dateFrom)->format('%r%a');
            if ($daysDiff < 0) {
                throw StayDatesOverlappingException::withDates($nextStay->dateFrom, $stay->dateTo);
            }
        }
    }
}
