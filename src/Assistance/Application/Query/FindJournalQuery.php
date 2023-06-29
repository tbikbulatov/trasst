<?php

declare(strict_types=1);

namespace App\Assistance\Application\Query;

use App\Assistance\Domain\ValueObject\JournalId;
use App\Shared\Application\Query\QueryInterface;

final readonly class FindJournalQuery implements QueryInterface
{
    public function __construct(
        public JournalId $id,
    ) {
    }
}
