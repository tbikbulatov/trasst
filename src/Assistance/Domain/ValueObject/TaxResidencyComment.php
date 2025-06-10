<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use Override;
use Stringable;

final readonly class TaxResidencyComment implements Stringable
{
    /**
     * @var array<non-empty-string>
     */
    public array $comments;

    /**
     * @param array<string> $comments
     */
    public function __construct(array $comments)
    {
        /** @var array<non-empty-string> $comments */
        $comments = array_filter($comments, fn ($value) => !empty(trim($value)));

        $this->comments = $comments;
    }

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

    #[Override]
    public function __toString(): string
    {
        return implode('; ', $this->comments);
    }
}
