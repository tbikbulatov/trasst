<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Assistance\Application\Command\AnalyzeJournalCommand;
use App\Assistance\Infrastructure\ApiPlatform\Resource\JournalResource;
use App\Shared\Application\Command\CommandBusInterface;

final readonly class AnalyzeJournalProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    /**
     * @param JournalResource $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof JournalResource);

        return $this->commandBus->dispatch(new AnalyzeJournalCommand($data->id));
    }
}
