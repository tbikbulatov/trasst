<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\TaxResidencyComment;
use App\Assistance\Domain\ValueObject\Year;
use App\Assistance\Domain\ValueObject\YearOutcome;
use DateInterval;
use DateTimeInterface;
use DomainException;

final readonly class DaysPerLast12MonthsRule implements CountryTaxResidencyRuleInterface
{
    private const MIN_DAYS = 1;
    private const MAX_DAYS = 365;
    private const PERIOD = 'P12M';

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
        return sprintf('Stay for %d days for the last 12 months', $this->daysForResidency);
    }

    public function check(CountryJournal $journal): array
    {
        /** @var array<YearOutcome> $outcomes */
        $outcomes = [];

        /** @var array<Stay> $processedStays */
        $processedStays = [];
        foreach ($journal as $stay) {
            for (
                $dateFrom = $stay->dateFrom, $daysCounted = 1; // amount of processed days in the current Stay
                $dateFrom <= $stay->dateTo;
                $dateFrom = $dateFrom->modify('+1 day'), $daysCounted = $dateFrom->diff($stay->dateFrom)->days + 1
            ) {
                $year = (int) $dateFrom->format('Y');
                $outcomes[$year] ??= $this->createYearOutcome($dateFrom, false);

                if ($this->isEnoughDaysForResidency($dateFrom, $processedStays, $daysCounted)) {
                    $outcomes[$year] = $this->createYearOutcome($dateFrom, true);
                    $dateFrom = $dateFrom->modify('31 December');
                }
            }
            $processedStays[] = $stay;
        }

        return $outcomes;
    }

    /**
     * @param array<Stay> $previousStays
     */
    private function isEnoughDaysForResidency(DateTimeInterface $dateFrom, array $previousStays, int $currentStayDays): bool
    {
        if ($currentStayDays >= $this->daysForResidency) {
            return true;
        }

        $edgeDate = $dateFrom->sub(new DateInterval(self::PERIOD));
        while (
            ($stay = array_pop($previousStays))
            && $stay->dateTo >= $edgeDate
            && $currentStayDays < $this->daysForResidency
        ) {
            $currentStayDays += $stay->dateFrom >= $edgeDate ? $stay->count() : $stay->dateTo->diff($edgeDate)->days + 1;
        }

        return $currentStayDays >= $this->daysForResidency;
    }

    private function createYearOutcome(DateTimeInterface $date, bool $isResident): YearOutcome
    {
        return new YearOutcome(
            Year::fromInt((int) $date->format('Y')),
            $isResident,
            TaxResidencyComment::single($this->getDescription())
        );
    }
}
