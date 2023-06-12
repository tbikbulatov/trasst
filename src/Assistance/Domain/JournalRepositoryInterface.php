<?php

declare(strict_types=1);

namespace App\Assistance\Domain;

use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\ValueObject\JournalId;

interface JournalRepositoryInterface
{
    public function get(JournalId $id): Journal;

    public function save(Journal $journal): void;
}
