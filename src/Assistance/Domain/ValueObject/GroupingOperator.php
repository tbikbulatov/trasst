<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

enum GroupingOperator: int
{
    case AND = 1;
    case OR = 2;
}
