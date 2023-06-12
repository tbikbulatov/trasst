<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\Repository;

use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\JournalRepositoryInterface;
use App\Assistance\Domain\ValueObject\JournalId;
use Symfony\Contracts\Cache\CacheInterface;

final readonly class CacheJournalRepository implements JournalRepositoryInterface
{
    public function __construct(
        private CacheInterface $journalCache,
    ) {}

    public function get(JournalId $id): Journal
    {
        return $this->journalCache->getItem($id->value)->get();
    }

    public function save(Journal $journal): void
    {
        $item = $this->journalCache->getItem($journal->getId()->value);
        $item->set($journal);

        $this->journalCache->save($item);
    }
}
