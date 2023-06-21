<?php

declare(strict_types=1);

namespace App\Assistance\Application\Command;

use App\Shared\Application\Command\CommandHandlerInterface;

final readonly class CreateJournalCommandHandler implements CommandHandlerInterface
{
    public function __construct(
    ) {
    }

    public function __invoke(CreateJournalCommand $command): string
    {
        return var_export($command, true);
    }
}
