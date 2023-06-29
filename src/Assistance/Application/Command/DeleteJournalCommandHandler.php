<?php

declare(strict_types=1);

namespace App\Assistance\Application\Command;

use App\Assistance\Domain\JournalRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;

final readonly class DeleteJournalCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private JournalRepositoryInterface $journalRepository,
    ) {
    }

    public function __invoke(DeleteJournalCommand $command): void
    {
        $journal = $this->journalRepository->get($command->id);

        $this->journalRepository->remove($journal);
    }
}
