<?php

declare(strict_types=1);

namespace App\Tests\Assistance\Domain\ValueObject;

use App\Assistance\Domain\Exception\StaysDatesOverlapsException;
use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\StayPurpose;
use App\Shared\Domain\Exception\ValidationException;
use DateTimeImmutable as Date;
use PHPUnit\Framework\TestCase;

final class CountryJournalTest extends TestCase
{
    public function testCountryJournalCantBeEmpty()
    {
        $this->expectException(ValidationException::class);

        new CountryJournal([]);
    }

    public function testCountryJournalMustContainOnlyOneCountryStays(): void
    {
        $stay1 = new Stay(CountryCode::ARMENIA, StayPurpose::TOURISM, new Date('2022-01-01'), new Date('2022-04-01'));
        $stay2 = new Stay(CountryCode::TURKEY, StayPurpose::TOURISM, new Date('2022-05-01'), new Date('2022-05-31'));

        $this->expectException(ValidationException::class);

        new CountryJournal([$stay1, $stay2]);
    }

    public function testCountryJournalKeepAllStays()
    {
        $country = CountryCode::ARMENIA;
        $purpose = StayPurpose::TOURISM;
        $stay1 = new Stay($country, $purpose, new Date('2022-01-01'), new Date('2022-04-01'));
        $stay2 = new Stay($country, $purpose, new Date('2022-05-01'), new Date('2022-06-01'));

        $sut = new CountryJournal([$stay1, $stay2]);

        $this->assertCount(2, $sut);
        $this->assertEquals($stay1, $sut->current());
        $sut->next();
        $this->assertEquals($stay2, $sut->current());
    }

    public function testCountryJournalCanWorkWithSingleStay(): void
    {
        $stay = new Stay(CountryCode::ARMENIA, StayPurpose::TOURISM, new Date('2022-01-01'), new Date('2022-04-01'));

        $sut = new CountryJournal([$stay]);

        $this->assertCount(1, $sut);
        $this->assertEquals($stay, $sut->current());
    }

    public function testCountryJournalAllowStayOverlapsForOneDayOnly(): void
    {
        $country = CountryCode::ARMENIA;
        $purpose = StayPurpose::TOURISM;
        $stay1 = new Stay($country, $purpose, new Date('2022-01-01'), new Date('2022-04-01'));
        $stay2 = new Stay($country, $purpose, new Date('2022-04-01'), new Date('2022-06-01'));

        $sut = new CountryJournal([$stay1, $stay2]);

        $this->assertCount(2, $sut);
    }

    public function testCountryJournalDenyStayOverlapsMoreThanOneDay(): void
    {
        $country = CountryCode::ARMENIA;
        $purpose = StayPurpose::TOURISM;
        $stay1 = new Stay($country, $purpose, new Date('2022-01-01'), new Date('2022-04-01'));
        $stay2 = new Stay($country, $purpose, new Date('2022-03-31'), new Date('2022-06-01'));

        $this->expectException(StaysDatesOverlapsException::class);

        new CountryJournal([$stay1, $stay2]);
    }
}
