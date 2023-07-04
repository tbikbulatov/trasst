<?php

declare(strict_types=1);

namespace App\Assistance\Domain\Exception;

final class JournalStaysDatesOverlapsException extends JournalValidationException
{
    public static function fromPrevious(StaysDatesOverlapsException $exception): self
    {
        return new self(self::MESSAGE_PREFIX . ': ' . $exception->getMessage());
    }
}
