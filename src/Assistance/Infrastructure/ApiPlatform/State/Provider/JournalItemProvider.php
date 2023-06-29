<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Assistance\Application\Common\JournalResult;
use App\Assistance\Application\Query\FindJournalQuery;
use App\Assistance\Domain\ValueObject\JournalId;
use App\Assistance\Infrastructure\ApiPlatform\Resource\JournalResource;
use App\Shared\Application\Query\QueryBusInterface;

/**
 * @implements ProviderInterface<JournalResource>
 */
final readonly class JournalItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?JournalResource
    {
        /** @var string $id */
        $id = $uriVariables['id'];

        /** @var JournalResult|null $result */
        $result = $this->queryBus->ask(new FindJournalQuery(new JournalId($id)));

        return $result ? JournalResource::fromResult($result) : null;
    }
}
