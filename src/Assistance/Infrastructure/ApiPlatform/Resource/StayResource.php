<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\StayPurpose;
use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Stay',
    types: ['https://schema.org/Thing'],
    operations: [
        new Get(controller: NotFoundAction::class),
    ],
)]
final readonly class StayResource
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Choice(callback: [CountryCode::class, 'cases'])]
        #[Assert\Type(CountryCode::class)]
        #[Groups(['read', 'write'])]
        public CountryCode $country,

        #[Assert\NotNull]
        #[Assert\Type(StayPurpose::class)]
        #[Groups(['read', 'write'])]
        public StayPurpose $purpose,

        #[Assert\NotNull]
        #[Assert\Type(DateTimeImmutable::class)]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        #[ApiProperty(openapiContext: ['type' => 'string', 'format' => 'date'])]
        #[Groups(['read', 'write'])]
        public DateTimeImmutable $dateFrom,

        #[Assert\NotNull]
        #[Assert\Type(DateTimeImmutable::class)]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
        #[ApiProperty(openapiContext: ['type' => 'string', 'format' => 'date'])]
        #[Groups(['read', 'write'])]
        public DateTimeImmutable $dateTo,
    ) {
    }

    public static function fromValueObject(Stay $s): self
    {
        return new self($s->country, $s->purpose, $s->dateFrom, $s->dateTo);
    }
}
