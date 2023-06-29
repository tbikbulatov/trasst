<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use App\Shared\Domain\ValueObject\EntityId;
use RuntimeException;

class EntityNotFoundException extends RuntimeException
{
    public static function withId(EntityId $id): static
    {
        return new static(sprintf('%s #%s not found', self::resolveEntityName(), $id));
    }

    private static function resolveEntityName(): string
    {
        $classname = get_called_class();

        if (!$pos = strrpos($classname, '\\')) {
            return 'Entity';
        }

        return substr($classname, $pos + 1, -strlen('NotFoundException'));
    }
}
