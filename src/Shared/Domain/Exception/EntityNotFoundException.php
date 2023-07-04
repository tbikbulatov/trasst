<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use App\Shared\Domain\ValueObject\EntityId;
use RuntimeException;
use Throwable;

class EntityNotFoundException extends RuntimeException
{
    final public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function withId(EntityId $id): static
    {
        return new static(sprintf('%s #%s not found', self::resolveEntityName(), (string) $id));
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
