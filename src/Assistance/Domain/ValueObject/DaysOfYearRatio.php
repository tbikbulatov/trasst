<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use BcMath\Number;
use DomainException;

final readonly class DaysOfYearRatio
{
    public Number $value;

    public function __construct(
        int|Number $dividend,
        int|Number $divisor = 1
    ) {
        if ($dividend < 0) {
            throw new DomainException("Dividend can't be negative");
        }
        $dividend = $dividend instanceof Number ? $dividend : new Number($dividend);

        if ($divisor <= 0) {
            throw new DomainException("Divisor can't be negative or zero");
        }
        $divisor = $divisor instanceof Number ? $divisor : new Number($divisor);

        $this->value = $dividend->div($divisor);
    }
}
