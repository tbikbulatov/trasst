<?php

declare(strict_types=1);

namespace App\Assistance\Infrastructure\ApiPlatform\Resource\Output;

use App\Assistance\Domain\ValueObject\CountryCode;

final readonly class CountryResidencyOutput
{
    public function __construct(
        public CountryCode $country,
        public bool $isResident,
        public ?string $residencyComment = null,
    ) {
    }
}
