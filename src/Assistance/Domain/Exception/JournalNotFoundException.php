<?php

declare(strict_types=1);

namespace App\Assistance\Domain\Exception;

use App\Shared\Domain\Exception\EntityNotFoundException;

final class JournalNotFoundException extends EntityNotFoundException
{
}
