<?php

declare(strict_types=1);

namespace App\Assistance\Application\Common;

use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\ValueObject\JournalId;
use App\Assistance\Domain\ValueObject\Stay;

final readonly class JournalResult
{
    public function __construct(
        public JournalId $journalId,

        /** @var array<Stay> $stays */
        public array $stays,
    ) {
    }

    public static function fromModel(Journal $journal): self
    {
        return new self($journal->getId(), $journal->getStays());
    }
}
