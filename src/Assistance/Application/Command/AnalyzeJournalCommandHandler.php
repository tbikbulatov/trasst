<?php

declare(strict_types=1);

namespace App\Assistance\Application\Command;

use App\Assistance\Domain\JournalRepositoryInterface;
use App\Assistance\Domain\TaxResidencyAnalyzer;
use App\Assistance\Domain\ValueObject\AnalysisOutcome;
use App\Shared\Application\Command\CommandHandlerInterface;

final readonly class AnalyzeJournalCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private JournalRepositoryInterface $journalRepository,
        private TaxResidencyAnalyzer $taxResidencyAnalyzer,
    ) {
    }

    public function __invoke(AnalyzeJournalCommand $command): AnalysisOutcome
    {
        $journal = $this->journalRepository->get($command->id);

        return $this->taxResidencyAnalyzer->analyze($journal);
    }
}
