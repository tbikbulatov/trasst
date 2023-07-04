<?php

declare(strict_types=1);

namespace App\Assistance\Domain\Exception;

use App\Shared\Domain\Exception\ValidationException;

class JournalValidationException extends ValidationException
{
    protected const MESSAGE_PREFIX = 'Journal validation error';
}
