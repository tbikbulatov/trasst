<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\TaxResidencyComment as Comment;
use App\Assistance\Domain\ValueObject\Year;
use App\Assistance\Domain\ValueObject\YearOutcome;
use DateTimeImmutable;
use DomainException;

final readonly class DaysPerCalendarYearRule implements CountryTaxResidencyRuleInterface
{
    private const MIN_DAYS = 1;
    private const MAX_DAYS = 365;

    public function __construct(
        public int $daysForResidency,
    ) {
        if ($this->daysForResidency < self::MIN_DAYS || $this->daysForResidency > self::MAX_DAYS) {
            throw new DomainException(sprintf(
                'Value must be in range %d - %d', self::MIN_DAYS, self::MAX_DAYS
            ));
        }
    }

    public function getDescription(): string
    {
        return sprintf('Stay for %d days in calendar year', $this->daysForResidency);
    }

    public function check(CountryJournal $journal): array
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
                $daysInYears[$year] += (new DateTimeImmutable("{$year}-12-31"))->diff($dateFrom)->days + 1;
            }

            $daysInYears[$yearTo] ??= 0;
            $daysInYears[$yearTo] += $stay->dateTo->diff($dateFrom)->days + 1;
        }

        $outcomes = [];
        foreach ($daysInYears as $year => $days) {
            $isResident = $days >= $this->daysForResidency;
            $outcomes[$year] = new YearOutcome(Year::fromInt($year), $isResident, Comment::single($this->getDescription()));
        }

        return $outcomes;
    }
}
