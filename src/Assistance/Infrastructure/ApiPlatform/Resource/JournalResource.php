<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\Assistance\Application\Common\JournalResult;
use App\Assistance\Domain\Exception\JournalNotFoundException;
use App\Assistance\Domain\Exception\JournalValidationException;
use App\Assistance\Domain\ValueObject\JournalId;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Infrastructure\ApiPlatform\Resource\Output\AnalysisOutcomeOutput;
use App\Assistance\Infrastructure\ApiPlatform\State\Processor\AnalyzeJournalProcessor;
use App\Assistance\Infrastructure\ApiPlatform\State\Processor\CreateJournalProcessor;
use App\Assistance\Infrastructure\ApiPlatform\State\Processor\DeleteJournalProcessor;
use App\Assistance\Infrastructure\ApiPlatform\State\Provider\JournalItemProvider;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Stays Journal.
 *
 * @psalm-suppress DeprecatedClass
 */
#[ApiResource(
    shortName: 'Journal',
    types: ['https://schema.org/Thing'],
    operations: [
        new Get(provider: JournalItemProvider::class),
        new GetCollection(controller: NotFoundAction::class, output: false, read: false),
        new Post(processor: CreateJournalProcessor::class),
        new Post(
            '/journals/{id}/analyze.{_format}',
            status: 200,
            openapi: new Operation(
                summary: 'Analyze a Journal.',
                description: 'Returns Journal analysis result',
                requestBody: new RequestBody(),
            ),
            normalizationContext: [AbstractObjectNormalizer::SKIP_NULL_VALUES => false],
            input: false,
            output: AnalysisOutcomeOutput::class,
            deserialize: false,
            validate: false,
            provider: JournalItemProvider::class,
            processor: AnalyzeJournalProcessor::class,
        ),
        new Delete(provider: JournalItemProvider::class, processor: DeleteJournalProcessor::class),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['read'],
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['write'],
        AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
    ],
    exceptionToStatus: [
        JournalNotFoundException::class => 404,
        JournalValidationException::class => 400,
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

        /** @var array<int,StayResource> $stays Stays */
        #[Assert\Count(min: 1)]
        #[ApiProperty(
            readable: true,
            writable: true,
            readableLink: true,
            writableLink: true,
            builtinTypes: [
                new Type(
                    builtinType: Type::BUILTIN_TYPE_ARRAY,
                    nullable: false,
                    collection: true,
                    collectionValueType: new Type(
                        builtinType: Type::BUILTIN_TYPE_OBJECT,
                        nullable: false,
                        class: StayResource::class,
                    ),
                ),
            ],
        )]
        #[Groups(['read', 'write'])]
        public array $stays = [],
    ) {
    }

    public static function fromResult(JournalResult $r): self
    {
        $makeStay = fn (Stay $s): StayResource => new StayResource($s->country, $s->purpose, $s->dateFrom, $s->dateTo);

        return new JournalResource($r->journalId, array_values(array_map($makeStay, $r->stays)));
    }
}
