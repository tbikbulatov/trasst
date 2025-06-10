<?php

declare(strict_types=1);

namespace App\Tests\Unit\Assistance\Domain\Entity;

use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\Exception\JournalHaveNoStaysException;
use App\Assistance\Domain\Exception\JournalStaysDatesOverlapsException;
use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\StayPurpose;
use App\Assistance\Infrastructure\IdGenerator\JournalIdGenerator;
use DateTimeImmutable as Date;
use PHPUnit\Framework\TestCase;

class JournalTest extends TestCase
{
    private JournalIdGenerator $journalIdGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalIdGenerator = new JournalIdGenerator();
    }

    public function testJournalCantBeEmpty()
    {
        $this->expectException(JournalHaveNoStaysException::class);

        new Journal($this->journalIdGenerator->generate(), []);
    }

    public function testJournalKeepAllStays()
    {
        $country = CountryCode::ARMENIA;
        $purpose = StayPurpose::TOURISM;
        $stay1 = new Stay($country, $purpose, new Date('2022-01-01'), new Date('2022-04-01'));
        $stay2 = new Stay($country, $purpose, new Date('2022-05-01'), new Date('2022-06-01'));

        $sut = new Journal($this->journalIdGenerator->generate(), [$stay1, $stay2]);

        $this->assertCount(2, $sut);
        $this->assertEquals($stay1, $sut->current());
        $sut->next();
        $this->assertEquals($stay2, $sut->current());
    }

    public function testJournalCanWorkWithSingleStay(): void
    {
        $stay = new Stay(CountryCode::ARMENIA, StayPurpose::TOURISM, new Date('2022-01-01'), new Date('2022-04-01'));

        $sut = new Journal($this->journalIdGenerator->generate(), [$stay]);

        $this->assertCount(1, $sut);
        $this->assertEquals($stay, $sut->current());
    }

    public function testJournalAllowStayOverlapsForOneDayOnly(): void
    {
        $stay1 = new Stay(CountryCode::ARMENIA, StayPurpose::TOURISM, new Date('2022-01-01'), new Date('2022-04-01'));
        $stay2 = new Stay(CountryCode::TURKEY, StayPurpose::TOURISM, new Date('2022-04-01'), new Date('2022-06-01'));

        $sut = new Journal($this->journalIdGenerator->generate(), [$stay1, $stay2]);

        $this->assertCount(2, $sut);
    }

    public function testJournalDenyStayOverlapsMoreThanOneDay(): void
    {
        $stay1 = new Stay(CountryCode::ARMENIA, StayPurpose::TOURISM, new Date('2022-01-01'), new Date('2022-04-01'));
        $stay2 = new Stay(CountryCode::TURKEY, StayPurpose::TOURISM, new Date('2022-03-31'), new Date('2022-06-01'));

        $this->expectException(JournalStaysDatesOverlapsException::class);

        new Journal($this->journalIdGenerator->generate(), [$stay1, $stay2]);
    }

    public function testSplitByCountriesGroupsStaysCorrectly(): void
    {
        $armenia = CountryCode::ARMENIA;
        $georgia = CountryCode::GEORGIA;
        $russia = CountryCode::RUSSIA;
        $turkey = CountryCode::TURKEY;
        $purpose = StayPurpose::TOURISM;
        $stays = [
            $a1 = new Stay($armenia, $purpose, new Date('2022-01-01'), new Date('2022-03-31')),
            $t1 = new Stay($turkey, $purpose, new Date('2022-04-01'), new Date('2022-04-14')),
            $t2 = new Stay($turkey, $purpose, new Date('2022-04-14'), new Date('2022-04-30')),
            $t3 = new Stay($turkey, $purpose, new Date('2022-07-01'), new Date('2023-01-31')),
            $g1 = new Stay($georgia, $purpose, new Date('2023-02-01'), new Date('2023-02-28')),
            $g2 = new Stay($georgia, $purpose, new Date('2023-03-01'), new Date('2023-03-31')),
            $r1 = new Stay($russia, $purpose, new Date('2023-05-01'), new Date('2023-05-31')),
        ];
        $sut = new Journal($this->journalIdGenerator->generate(), $stays);

        $countryJournals = $sut->splitByCountries();

        $this->assertCount(4, $countryJournals);
        $this->assertEmpty(array_diff(
            [$armenia->value, $georgia->value, $russia->value, $turkey->value],
            array_keys($countryJournals)
        ));
        $this->assertCount(1, $countryJournals[$armenia->value]);
        $this->assertCount(3, $countryJournals[$turkey->value]);
        $this->assertCount(2, $countryJournals[$georgia->value]);
        $this->assertCount(1, $countryJournals[$russia->value]);
        $this->assertEquals($a1, $countryJournals[$armenia->value]->current());
        $this->assertEquals($t1, $countryJournals[$turkey->value]->current());
        $countryJournals[$turkey->value]->next();
        $this->assertEquals($t2, $countryJournals[$turkey->value]->current());
        $countryJournals[$turkey->value]->next();
        $this->assertEquals($t3, $countryJournals[$turkey->value]->current());
        $this->assertEquals($g1, $countryJournals[$georgia->value]->current());
        $countryJournals[$georgia->value]->next();
        $this->assertEquals($g2, $countryJournals[$georgia->value]->current());
        $this->assertEquals($r1, $countryJournals[$russia->value]->current());
    }
}
