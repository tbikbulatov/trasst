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
     * @param array<string,mixed> $uriVariables
     * @param array<string,mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JournalResource
    {
        assert($data instanceof JournalResource);

        $command = $this->createCommand($data);

        /** @var JournalResult $result */
        $result = $this->commandBus->dispatch($command);

        return JournalResource::fromResult($result);
    }

    private function createCommand(JournalResource $r): CreateJournalCommand
    {
        /** @var array<int,Stay> */
        $a = array_map(fn (StayResource $s) => new Stay($s->country, $s->purpose, $s->dateFrom, $s->dateTo), $r->stays);

        return new CreateJournalCommand($a);
    }
}
