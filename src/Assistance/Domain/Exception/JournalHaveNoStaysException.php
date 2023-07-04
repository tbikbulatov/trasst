<?php

declare(strict_types=1);

namespace App\Assistance\Domain\Exception;

use Throwable;

final class JournalHaveNoStaysException extends JournalValidationException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $message = self::MESSAGE_PREFIX.': '.(empty($message) ? 'Journal must contain stays' : $message);

        parent::__construct($message, $code, $previous);
    }
}
