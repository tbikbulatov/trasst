<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\Repository;

use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\Exception\JournalNotFoundException;
use App\Assistance\Domain\JournalRepositoryInterface;
use App\Assistance\Domain\ValueObject\JournalId;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\CacheInterface;

final readonly class CacheJournalRepository implements JournalRepositoryInterface
{
    public function __construct(
        /** @var CacheInterface&CacheItemPoolInterface $journalCache */
        private CacheInterface $journalCache,
    ) {
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     */
    public function get(JournalId $id): Journal
    {
        /** @var CacheItemInterface $cacheItem */
        $cacheItem = $this->journalCache->getItem($id->value);
        if (!$cacheItem->isHit()) {
            throw JournalNotFoundException::withId($id);
        }

        return $cacheItem->get();
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     */
    public function findOne(JournalId $id): ?Journal
    {
        return $this->journalCache->getItem($id->value)->get();
    }

    public function remove(Journal $journal): void
    {
        $this->journalCache->delete($journal->getId()->value);
    }

    public function save(Journal $journal): void
    {
        /** @var CacheItemInterface $cacheItem */
        $cacheItem = $this->journalCache->getItem($journal->getId()->value);
        $cacheItem->set($journal);

        $this->journalCache->save($cacheItem);
    }
}
