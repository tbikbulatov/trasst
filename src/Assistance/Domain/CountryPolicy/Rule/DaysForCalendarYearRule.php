<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\Traits\CountryJournalYearDaysCountingTrait;
use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\TaxResidencyComment as Comment;
use App\Assistance\Domain\ValueObject\Year;
use App\Assistance\Domain\ValueObject\YearOutcome;
use DomainException;
use Override;

final readonly class DaysForCalendarYearRule implements CountryTaxResidencyRuleInterface
{
    use CountryJournalYearDaysCountingTrait;

    private const MIN_DAYS = 1;
    private const MAX_DAYS = 365;

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
        return sprintf('Stay for %d days in calendar year', $this->daysToResidency);
    }

    #[Override]
    public function check(CountryJournal $journal): array
    {
        $daysInYears = $this->countDaysInYears($journal);

        $outcomes = [];
        foreach ($daysInYears as $year => $days) {
            $outcomes[$year] = $days >= $this->daysToResidency
                ? YearOutcome::resident(Year::fromInt($year), Comment::single($this->getDescription()))
                : YearOutcome::notResident(Year::fromInt($year));
        }

        return $outcomes;
    }
}
