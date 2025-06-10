<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\YearOutcome;

interface CountryTaxResidencyRuleInterface
{
    public function getDescription(): string;

    /**
     * @return array<int, YearOutcome> Indexed by years
     */
    public function check(CountryJournal $journal): array;
}
