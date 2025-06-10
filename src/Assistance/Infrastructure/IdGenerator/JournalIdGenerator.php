<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\IdGenerator;

use App\Assistance\Domain\JournalIdGeneratorInterface;
use App\Assistance\Domain\ValueObject\JournalId;
use App\Shared\Infrastructure\IdGenerator\SymfonyUuidV7Generator;
use Override;

final class JournalIdGenerator extends SymfonyUuidV7Generator implements JournalIdGeneratorInterface
{
    #[Override]
    public function generate(): JournalId
    {
        return new JournalId(self::randomInRfc4122());
    }
}
