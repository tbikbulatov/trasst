<?php

declare(strict_types=1);

namespace App\Assistance\Domain;

use App\Assistance\Domain\ValueObject\JournalId;

interface JournalIdGeneratorInterface
{
    public function generate(): JournalId;
}
