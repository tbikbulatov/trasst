<?php

namespace App\Tests\Assistance\Domain\ValueObject;

use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\StayPurpose;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class StayTest extends TestCase
{
    public function testStayKeepInitValues(): void
    {
        $country = CountryCode::ARMENIA;
        $purpose = StayPurpose::TOURISM;
        $dateFrom = new DateTimeImmutable('2022-01-01');
        $dateTo = new DateTimeImmutable('2022-04-01');

        $sut = new Stay($country, $purpose, $dateFrom, $dateTo);

        self::assertEquals($country, $sut->country);
        self::assertEquals($purpose, $sut->purpose);
        self::assertEquals($dateFrom, $sut->dateFrom);
        self::assertEquals($dateTo, $sut->dateTo);
    }

    public function testStayAssignDatesPropertiesCorrectly(): void
    {
        $country = CountryCode::ARMENIA;
        $purpose = StayPurpose::TOURISM;
        $swappedDateFrom = new DateTimeImmutable('2022-04-01');
        $swappedDateTo = new DateTimeImmutable('2022-01-01');

        $sut = new Stay($country, $purpose, $swappedDateTo, $swappedDateFrom);

        self::assertEquals($swappedDateFrom, $sut->dateTo);
        self::assertEquals($swappedDateTo, $sut->dateFrom);
    }

}
