<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use Countable;
use DateTimeImmutable as Date;
use Override;

final readonly class Stay implements Countable
{
    public CountryCode $country;
    public StayPurpose $purpose;
    public Date $dateFrom;
    public Date $dateTo;

    public function __construct(CountryCode $country, StayPurpose $purpose, Date $date1, Date $date2)
    {
        $this->country = $country;
        $this->purpose = $purpose;
        [$this->dateFrom, $this->dateTo] = $date1 <= $date2 ? [$date1, $date2] : [$date2, $date1];
    }

    /**
     * Returns number of days in an interval.
     *
     * {@inheritDoc}
     */
    #[Override]
    public function count(): int
    {
        return (int) $this->dateTo->diff($this->dateFrom)->days + 1;
    }
}
