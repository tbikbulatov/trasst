<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Assistance\Application\Common\JournalResult;
use App\Assistance\Domain\ValueObject\JournalId;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Infrastructure\ApiPlatform\State\Processor\CreateJournalProcessor;
use App\Assistance\Infrastructure\ApiPlatform\State\Processor\DeleteJournalProcessor;
use App\Assistance\Infrastructure\ApiPlatform\State\Provider\JournalItemProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/** A Stays Journal */
#[ApiResource(
    shortName: 'Journal',
    operations: [
        new Get(provider: JournalItemProvider::class),
        new GetCollection(controller: NotFoundAction::class, output: false, read: false),
        new Post(processor: CreateJournalProcessor::class),
        new Delete(provider: JournalItemProvider::class, processor: DeleteJournalProcessor::class),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: [
        'disable_type_enforcement' => true,
        'groups' => ['write'],
    ],
)]
final class JournalResource
{
    public function __construct(
        /** The ID of this journal */
        #[Assert\Uuid]
        #[ApiProperty(
            readable: true,
            writable: false,
            identifier: true,
            openapiContext: ['type' => 'string', 'format' => 'uuid']
        )]
        #[Groups(['read'])]
        public ?JournalId $id = null,

        /** @var StayResource[] $stays Stays */
        #[Assert\NotBlank]
        #[Assert\Type('array')]
        #[Assert\Count(['min' => 1])]
        #[Assert\All([
            new Assert\Type(StayResource::class)
        ])]
        #[Assert\Valid]
        #[Groups(['read', 'write'])]
        public array $stays = [],
    ) {
    }

    public function addStay(StayResource $stay): self
    {
        $this->stays[] = $stay;

        return $this;
    }

    public static function fromResult(JournalResult $r): self
    {
        return new JournalResource(
            $r->journalId,
            array_map(fn(Stay $s) => new StayResource($s->country, $s->purpose, $s->dateFrom, $s->dateTo), $r->stays)
        );
    }
}
