<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Stringable;

abstract readonly class EntityId implements Stringable
{
    public function __construct(
        public string $value,
    ) {
    }

    public function equals(self $anotherId): bool
    {
        return get_class($this) === get_class($anotherId) && $this->value === $anotherId->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
