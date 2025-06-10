<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\TaxResidencyComment;
use App\Assistance\Domain\ValueObject\Year;
use App\Assistance\Domain\ValueObject\YearOutcome;
use DateInterval;
use DateTimeImmutable;
use DomainException;
use Override;

final readonly class DaysForLast12MonthsRule implements CountryTaxResidencyRuleInterface
{
    private const MIN_DAYS = 1;
    private const MAX_DAYS = 365;
    private const PERIOD = 'P12M';

    public function __construct(
        private int $daysToResidency,
    ) {
        if ($this->daysToResidency < self::MIN_DAYS || $this->daysToResidency > self::MAX_DAYS) {
            throw new DomainException(sprintf('Value must be in range %d - %d', self::MIN_DAYS, self::MAX_DAYS));
        }
    }

    #[Override]
    public function getDescription(): string
    {
        return sprintf('Stay for %d days for the last 12 months', $this->daysToResidency);
    }

    #[Override]
    public function check(CountryJournal $journal): array
    {
        /** @var array<int, YearOutcome> $outcomes */
        $outcomes = [];

        /** @var array<Stay> $processedStays */
        $processedStays = [];
        foreach ($journal as $stay) {
            for (
                $dateFrom = $stay->dateFrom, $daysCounted = 1; // number of processed days in the current Stay
                $dateFrom <= $stay->dateTo;
                $dateFrom = $dateFrom->modify('+1 day'), $daysCounted = (int) $dateFrom->diff($stay->dateFrom)->days + 1
            ) {
                $year = (int) $dateFrom->format('Y');
                $outcomes[$year] ??= YearOutcome::notResident(Year::fromInt($year));

                if ($this->isEnoughDaysToResidency($dateFrom, $processedStays, $daysCounted)) {
                    $outcomes[$year] = YearOutcome::resident(Year::fromInt($year), TaxResidencyComment::single($this->getDescription()));
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
    private function isEnoughDaysToResidency(DateTimeImmutable $dateFrom, array $previousStays, int $currentStayDays): bool
    {
        if ($currentStayDays >= $this->daysToResidency) {
            return true;
        }

        $edgeDate = $dateFrom->sub(new DateInterval(self::PERIOD));
        while (
            ($stay = array_pop($previousStays))
            && $stay->dateTo >= $edgeDate
            && $currentStayDays < $this->daysToResidency
        ) {
            $currentStayDays += $stay->dateFrom >= $edgeDate ? $stay->count() : (int) $stay->dateTo->diff($edgeDate)->days + 1;
        }

        return $currentStayDays >= $this->daysToResidency;
    }
}
