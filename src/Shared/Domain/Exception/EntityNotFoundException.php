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
        return new static(sprintf('%s #%s not found', self::resolveEntityName() ?? 'Entity', (string) $id));
    }

    private static function resolveEntityName(): ?string
    {
        $shortClassName = basename(str_replace('\\', '/', get_called_class()));
        $suffix = 'NotFoundException';

        if (str_ends_with($shortClassName, $suffix)) {
            return substr($shortClassName, 0, -strlen($suffix));
        }

        return null;
    }
}
