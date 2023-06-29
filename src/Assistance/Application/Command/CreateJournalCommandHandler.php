<?php

declare(strict_types=1);

namespace App\Assistance\Application\Command;

use App\Assistance\Application\Common\JournalResult;
use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\JournalIdGeneratorInterface;
use App\Assistance\Domain\JournalRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;

final readonly class CreateJournalCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private JournalIdGeneratorInterface $journalIdGenerator,
        private JournalRepositoryInterface $journalRepository,
    ) {
    }

    public function __invoke(CreateJournalCommand $command): JournalResult
    {
        $journal = new Journal($this->journalIdGenerator->generate(), $command->stays);
        $this->journalRepository->save($journal);

        return new JournalResult($journal->getId(), $journal->getStays());
    }
}
