<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\Resource\Output;

use App\Assistance\Domain\ValueObject\AnalysisOutcome;

final readonly class AnalysisOutcomeOutput
{
    public function __construct(
        /** @var array<int, array<CountryResidencyOutput>> $years */
        public array $years = [],
    ) {
    }

    public static function fromValueObject(AnalysisOutcome $outcome): self
    {
        $years = [];
        foreach ($outcome as $countryOutcome) {
            foreach ($countryOutcome->yearsOutcomes as $year) {
                $years[$year->year->toInt()][] = new CountryResidencyOutput(
                    $countryOutcome->country,
                    $year->isResident,
                    $year->residencyComment?->__toString(),
                );
            }
        }

        return new self($years);
    }
}
