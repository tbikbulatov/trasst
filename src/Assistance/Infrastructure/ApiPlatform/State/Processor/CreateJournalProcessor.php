<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Assistance\Application\Command\CreateJournalCommand;
use App\Assistance\Application\Common\JournalResult;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Infrastructure\ApiPlatform\Resource\JournalResource;
use App\Assistance\Infrastructure\ApiPlatform\Resource\StayResource;
use App\Shared\Application\Command\CommandBusInterface;

final readonly class CreateJournalProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    /**
     * @param JournalResource $data
     * @return JournalResource
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof JournalResource);

        $command = $this->createCommand($data);

        /** @var JournalResult $result */
        $result = $this->commandBus->dispatch($command);

        return JournalResource::fromResult($result);
    }

    /**
     * @param JournalResource $r
     * @return CreateJournalCommand
     */
    private function createCommand(JournalResource $r): CreateJournalCommand
    {
        return new CreateJournalCommand(
            array_map(fn(StayResource $s) => new Stay($s->country, $s->purpose, $s->dateFrom, $s->dateTo), $r->stays)
        );
    }
}
