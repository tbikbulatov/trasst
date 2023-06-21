<?php

declare(strict_types=1);

namespace App\Assistance\Application\Command;

use App\Assistance\Domain\ValueObject\Stay;
use App\Shared\Application\Command\CommandInterface;

final readonly class CreateJournalCommand implements CommandInterface
{
    public function __construct(
        /** @var array<Stay> $stays */
        public array $stays,
    ) {
    }
}
