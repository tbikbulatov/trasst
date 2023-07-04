<?php

declare(strict_types=1);

namespace App\Tests\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\TaxResidencyComment;
use App\Assistance\Domain\ValueObject\Year;
use App\Assistance\Domain\ValueObject\YearOutcome;

final readonly class DummyResidencyRule implements CountryTaxResidencyRuleInterface
{
    public function __construct(
        private Year $year,
    ) {
    }

    public function getDescription(): string
    {
        return 'Dummy residency rule';
    }

    public function check(CountryJournal $journal): array
    {
        return [
            YearOutcome::resident($this->year, TaxResidencyComment::single('Tax residency comment')),
        ];
    }
}
