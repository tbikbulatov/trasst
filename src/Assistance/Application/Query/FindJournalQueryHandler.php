<?php

declare(strict_types=1);

namespace App\Assistance\Application\Query;

use App\Assistance\Application\Common\JournalResult;
use App\Assistance\Domain\JournalRepositoryInterface;
use App\Shared\Application\Query\QueryHandlerInterface;

final readonly class FindJournalQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private JournalRepositoryInterface $repository
    ) {
    }

    public function __invoke(FindJournalQuery $query): JournalResult
    {
        $journal = $this->repository->get($query->id);

        return JournalResult::fromModel($journal);
    }
}
