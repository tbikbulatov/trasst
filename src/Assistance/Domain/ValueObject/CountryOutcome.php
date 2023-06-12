<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use Countable;

final readonly class CountryOutcome implements Countable
{
    public CountryCode $country;

    /** @var array<YearOutcome> $yearsOutcomes */
    public array $yearsOutcomes;

    /**
     * @param array<YearOutcome> $yearsOutcomes
     */
    public function __construct(CountryCode $country, array $yearsOutcomes)
    {
        $this->country = $country;
        $this->yearsOutcomes = $this->groupByYear($yearsOutcomes);
    }

    public function count(): int
    {
        return count($this->yearsOutcomes);
    }

    /**
     * @param array<YearOutcome> $yearsOutcomes
     * @return array<YearOutcome>
     */
    private function groupByYear(array $yearsOutcomes): array
    {
        /** @var array<YearOutcome> $grouped */
        $grouped = [];
        foreach ($yearsOutcomes as $outcome) {
            $year = $outcome->year->toInt();

            $grouped[$year] ??= YearOutcome::emptyForYear($outcome->year);
            $grouped[$year] = $grouped[$year]->sumUp($outcome);
        }

        return $grouped;
    }
}
