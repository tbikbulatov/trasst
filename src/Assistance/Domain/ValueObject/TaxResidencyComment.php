<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use Stringable;

final readonly class TaxResidencyComment implements Stringable
{
    public function __construct(
        /** @var array<string> $comments */
        public array $comments,
    ) {}

    public static function single(string $comment): self
    {
        return new self([$comment]);
    }

    public function concat(self|string $comment): self
    {
        return new self(array_merge(
            $this->comments,
            is_string($comment) ? [$comment] : $comment->comments
        ));
    }

    public function __toString(): string
    {
        return implode('; ', $this->comments);
    }
}
