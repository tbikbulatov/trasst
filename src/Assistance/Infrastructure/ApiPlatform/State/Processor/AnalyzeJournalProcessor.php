<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Assistance\Application\Command\AnalyzeJournalCommand;
use App\Assistance\Domain\ValueObject\AnalysisOutcome;
use App\Assistance\Infrastructure\ApiPlatform\Resource\JournalResource;
use App\Assistance\Infrastructure\ApiPlatform\Resource\Output\AnalysisOutcomeOutput;
use App\Shared\Application\Command\CommandBusInterface;
use Override;

/**
 * @implements ProcessorInterface<JournalResource, AnalysisOutcomeOutput>
 */
final readonly class AnalyzeJournalProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    /**
     * @param array<string,mixed> $uriVariables
     * @param array<string,mixed> $context
     */
    #[Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AnalysisOutcomeOutput
    {
        assert($data instanceof JournalResource);
        assert(isset($data->id));

        /** @var AnalysisOutcome $outcome */
        $outcome = $this->commandBus->dispatch(new AnalyzeJournalCommand($data->id));

        return AnalysisOutcomeOutput::fromValueObject($outcome);
    }
}
