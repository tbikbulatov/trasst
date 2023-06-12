<?php

declare(strict_types=1);

namespace App\Tests\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\YearOutcome;
use App\Assistance\Domain\ValueObject\Year;

final readonly class DummyNoResidencyRule implements CountryTaxResidencyRuleInterface
{
    public function __construct(
        private Year $year,
    ) {}

    public function getDescription(): string
    {
        return 'Dummy no residency rule';
    }

    public function check(CountryJournal $journal): array
    {
        return [
            new YearOutcome($this->year, false),
        ];
    }
}
