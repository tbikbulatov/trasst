<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\Traits\CountryJournalYearDaysCountingTrait;
use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\DaysOfYearRatio;
use App\Assistance\Domain\ValueObject\TaxResidencyComment as Comment;
use App\Assistance\Domain\ValueObject\Year;
use App\Assistance\Domain\ValueObject\YearOutcome;
use BcMath\Number;
use DomainException;
use Override;

final readonly class DaysForLastSequentialYearsInProportionRule implements CountryTaxResidencyRuleInterface
{
    use CountryJournalYearDaysCountingTrait;

    /** @var DaysOfYearRatio[] */
    private array $daysPerYearsRatios;

    public function __construct(
        private int $daysToResidency,
        DaysOfYearRatio ...$daysPerYearsRatios,
    ) {
        if ($this->daysToResidency < 1) {
            throw new DomainException('Value must be positive');
        }

        if (!$daysPerYearsRatios) {
            throw new DomainException('The rule must be initialized by at least one DaysOfYearRatio object');
        }
        $this->daysPerYearsRatios = $daysPerYearsRatios;
    }

    #[Override]
    public function getDescription(): string
    {
        return sprintf('Stay for %d days for current and past years (in proportion)', $this->daysToResidency);
    }

    #[Override]
    public function check(CountryJournal $journal): array
    {
        $daysInYears = $this->countDaysInYears($journal);

        $outcomes = [];
        foreach (array_keys($daysInYears) as $year) {
            $daysCounter = new Number(0);

            foreach ($this->daysPerYearsRatios as $index => $daysRatio) {
                $daysInYear = new Number($daysInYears[$year - (int) $index] ?? 0);
                $daysCounter = $daysCounter->add(
                    $daysInYear->mul($daysRatio->value)->ceil()
                );
            }

            $outcomes[$year] = $daysCounter >= $this->daysToResidency
                ? YearOutcome::resident(Year::fromInt($year), Comment::single($this->getDescription()))
                : YearOutcome::notResident(Year::fromInt($year));
        }

        return $outcomes;
    }
}
