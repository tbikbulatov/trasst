<?php

declare(strict_types=1);

namespace App\Assistance\Domain;

use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\Exception\JournalNotFoundException;
use App\Assistance\Domain\ValueObject\JournalId;

interface JournalRepositoryInterface
{
    /**
     * @throws JournalNotFoundException
     */
    public function get(JournalId $id): Journal;

    public function findOne(JournalId $id): ?Journal;

    public function remove(Journal $journal): void;

    public function save(Journal $journal): void;
}
