<?php

declare(strict_types=1);

namespace App\Assistance\Application\Command;

use App\Assistance\Domain\ValueObject\JournalId;
use App\Shared\Application\Command\CommandInterface;

final readonly class AnalyzeJournalCommand implements CommandInterface
{
    public function __construct(
        public JournalId $id,
    ) {
    }
}
