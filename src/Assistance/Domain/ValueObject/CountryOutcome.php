<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

final readonly class CountryOutcome
{
    /**
     * @param array<int, YearOutcome> $yearsOutcomes Indexed by years
     */
    public function __construct(
        public CountryCode $country,
        public array $yearsOutcomes,
    ) {
    }
}
