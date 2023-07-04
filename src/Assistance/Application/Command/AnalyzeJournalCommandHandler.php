<?php

declare(strict_types=1);

namespace App\Assistance\Application\Command;

use App\Assistance\Domain\JournalRepositoryInterface;
use App\Assistance\Domain\TaxResidencyAnalyzer;
use App\Assistance\Infrastructure\ApiPlatform\Resource\Output\AnalysisOutcomeOutput;
use App\Shared\Application\Command\CommandHandlerInterface;

final readonly class AnalyzeJournalCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private JournalRepositoryInterface $journalRepository,
        private TaxResidencyAnalyzer $taxResidencyAnalyzer,
    ) {
    }

    public function __invoke(AnalyzeJournalCommand $command): AnalysisOutcomeOutput
    {
        $journal = $this->journalRepository->get($command->id);
        $outcome = $this->taxResidencyAnalyzer->analyze($journal);

        return AnalysisOutcomeOutput::fromValueObject($outcome);
    }
}
