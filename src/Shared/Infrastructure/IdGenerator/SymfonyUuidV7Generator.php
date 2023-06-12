<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\IdGenerator;
use Symfony\Component\Uid\Uuid;

class SymfonyUuidV7Generator
{
    public static function fromString(string $uuid): Uuid
    {
        return Uuid::fromString($uuid);
    }

    public static function random(): Uuid
    {
        return Uuid::v7();
    }

    public static function randomInRfc4122(): string
    {
        return static::random()->toRfc4122();
    }
}
