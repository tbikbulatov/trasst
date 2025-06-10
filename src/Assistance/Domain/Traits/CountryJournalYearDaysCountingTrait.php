<?php

declare(strict_types=1);

namespace App\Assistance\Domain\Traits;

use App\Assistance\Domain\ValueObject\CountryJournal;
use DateTimeImmutable;

trait CountryJournalYearDaysCountingTrait
{
    /**
     * @return array<int,int> Key is a year, value is the number of days stayed in the year
     */
    public function countDaysInYears(CountryJournal $journal): array
    {
        $daysInYears = [];
        foreach ($journal as $stay) {
            $dateFrom = $stay->dateFrom;
            $yearTo = (int) $stay->dateTo->format('Y');

            for (
                $year = (int) $dateFrom->format('Y');
                $year < $yearTo;
                $year++, $dateFrom = new DateTimeImmutable("{$year}-01-01")
            ) {
                $daysInYears[$year] ??= 0;
                $daysInYears[$year] += (int) (new DateTimeImmutable("{$year}-12-31"))->diff($dateFrom)->days + 1;
            }

            $daysInYears[$yearTo] ??= 0;
            $daysInYears[$yearTo] += (int) $stay->dateTo->diff($dateFrom)->days + 1;
        }

        return $daysInYears;
    }
}
