<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\YearOutcome;

interface CountryTaxResidencyRuleInterface
{
    /**
     * @return non-empty-string
     */
    public function getDescription(): string;

    /**
     * @return array<YearOutcome>
     */
    public function check(CountryJournal $journal): array;
}
