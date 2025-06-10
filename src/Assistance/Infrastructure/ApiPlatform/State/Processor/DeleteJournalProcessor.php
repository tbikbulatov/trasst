<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Assistance\Application\Command\DeleteJournalCommand;
use App\Assistance\Infrastructure\ApiPlatform\Resource\JournalResource;
use App\Shared\Application\Command\CommandBusInterface;
use Override;

/**
 * @implements ProcessorInterface<JournalResource, void>
 */
final readonly class DeleteJournalProcessor implements ProcessorInterface
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
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        assert($data instanceof JournalResource);
        assert(isset($data->id));

        $this->commandBus->dispatch(new DeleteJournalCommand($data->id));
    }
}
