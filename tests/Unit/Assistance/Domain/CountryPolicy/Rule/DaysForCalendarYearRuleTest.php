<?php

declare(strict_types=1);

namespace App\Tests\Unit\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\CountryPolicy\Rule\DaysForCalendarYearRule;
use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\StayPurpose;
use App\Assistance\Domain\ValueObject\YearOutcome;
use DateTimeImmutable as Date;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DaysForCalendarYearRuleTest extends TestCase
{
    #[DataProvider('validDaysAmountForRuleConfigurationProvider')]
    public function testAmountOfDaysToResidencyShouldBeInYearInterval(int $days): void
    {
        $sut = new DaysForCalendarYearRule($days);

        self::assertNotEmpty($sut);
    }

    public static function validDaysAmountForRuleConfigurationProvider(): array
    {
        return [
            '1 day' => [1],
            '10 days' => [10],
            '100 days' => [100],
            '365 days' => [365],
        ];
    }

    #[DataProvider('invalidDaysAmountForRuleConfigurationProvider')]
    public function testItShouldThrowExceptionWhenSetupInvalidAmountOfDaysToResidency(int $days): void
    {
        $this->expectException(DomainException::class);

        new DaysForCalendarYearRule($days);
    }

    public static function invalidDaysAmountForRuleConfigurationProvider(): array
    {
        return [
            '-10 days' => [-10],
            '-1 day' => [-1],
            '0 days' => [0],
            '366 days' => [366],
            '1000 days' => [1000],
        ];
    }

    #[DataProvider('validSingleStaysThatDontLeadToResidencyProvider')]
    public function testOneStayWithinOneYearForLessDaysThanRequiredShouldNotLeadToResidency(
        int $daysToResidency,
        Stay $stay,
    ): void {
        $sut = new DaysForCalendarYearRule($daysToResidency);

        $outcome = $sut->check(new CountryJournal([$stay]));

        foreach ($outcome as $yearOutcome) {
            $this->assertFalse($yearOutcome->isResident);
        }
    }

    /**
     * @return array<string,Stay>
     */
    public static function validSingleStaysThatDontLeadToResidencyProvider(): array
    {
        $c = CountryCode::any();
        $p = StayPurpose::TOURISM;

        return [
            '14d res, 1d stay' => [14, new Stay($c, $p, new Date('2021-10-01'), new Date('2021-10-01'))],
            '14d res, 2d stay' => [14, new Stay($c, $p, new Date('2021-10-01'), new Date('2021-10-02'))],
            '14d res, 7d stay' => [14, new Stay($c, $p, new Date('2021-10-01'), new Date('2021-10-07'))],
            '14d res, 13d stay' => [14, new Stay($c, $p, new Date('2021-10-01'), new Date('2021-10-13'))],
            '183d res, 1d stay' => [183, new Stay($c, $p, new Date('2021-10-01'), new Date('2021-10-01'))],
            '183d res, 100d stay' => [183, new Stay($c, $p, new Date('2021-10-01'), new Date('2022-01-08'))],
            '183d res, 181d stay' => [183, new Stay($c, $p, new Date('2021-10-01'), new Date('2022-03-31'))],
            '183d res, 182d stay' => [183, new Stay($c, $p, new Date('2021-10-01'), new Date('2022-04-01'))],
            '183d res, 182d stay-per-year' => [183, new Stay($c, $p, new Date('2021-07-03'), new Date('2022-07-01'))],
        ];
    }

    public function testMultipleStaysWithinOneYearForLessDaysThanRequiredShouldNotLeadToResidency(): void
    {
        $country = CountryCode::any();
        $purpose = StayPurpose::TOURISM;
        $journal = new CountryJournal([
            new Stay($country, $purpose, new Date('2021-01-01'), new Date('2021-03-31')), // 90d
            new Stay($country, $purpose, new Date('2021-06-01'), new Date('2021-06-30')), // 30d
            new Stay($country, $purpose, new Date('2021-12-01'), new Date('2021-12-31')), // 31d
        ]);
        $sut = new DaysForCalendarYearRule(183);

        $outcome = $sut->check($journal);

        $this->assertCount(1, $outcome, 'One-year outcome');
        $this->assertEquals([2021], $this->extractYears($outcome));
        $this->assertFalse(current($outcome)->isResident);
    }

    public function testOneStayForTwoYearsForLessDaysInEachThanRequiredShouldNotLeadToResidency(): void
    {
        $journal = new CountryJournal([
            new Stay(CountryCode::any(), StayPurpose::TOURISM, new Date('2020-07-03'), new Date('2021-07-01')),
        ]);
        $sut = new DaysForCalendarYearRule(183);

        $outcome = $sut->check($journal);

        $this->assertEquals([2020, 2021], $this->extractYears($outcome));
        foreach ($outcome as $yearOutcome) {
            $this->assertFalse($yearOutcome->isResident);
        }
    }

    public function testMultipleStaysForMultipleYearsForLessDaysInEachThanRequiredShouldNotLeadToResidency(): void
    {
        $country = CountryCode::any();
        $purpose = StayPurpose::TOURISM;
        $journal = new CountryJournal([
            new Stay($country, $purpose, new Date('2019-01-01'), new Date('2019-04-01')), // 91d
            new Stay($country, $purpose, new Date('2019-04-03'), new Date('2019-07-01')), // 90d
            new Stay($country, $purpose, new Date('2020-07-03'), new Date('2020-09-01')), // 61d
            new Stay($country, $purpose, new Date('2020-09-02'), new Date('2020-11-30')), // 90d
            new Stay($country, $purpose, new Date('2021-04-10'), new Date('2021-10-08')), // 182d
            new Stay($country, $purpose, new Date('2022-10-01'), new Date('2022-12-31')), // 92d
            new Stay($country, $purpose, new Date('2023-01-01'), new Date('2023-05-31')), // 151d
        ]);
        $sut = new DaysForCalendarYearRule(183);

        $outcome = $sut->check($journal);

        $this->assertEquals([2019, 2020, 2021, 2022, 2023], $this->extractYears($outcome));
        foreach ($outcome as $yearOutcome) {
            $this->assertFalse($yearOutcome->isResident);
        }
    }

    #[DataProvider('journalWithEnoughDaysToResidencyProvider')]
    public function testOneStayWithinOneYearForEnoughDaysShouldLeadToResidency(
        int $daysToResidency,
        CountryJournal $journal,
    ): void {
        $sut = new DaysForCalendarYearRule($daysToResidency);

        $outcome = $sut->check($journal);

        $this->assertTrue(current($outcome)->isResident);
    }

    /**
     * @return array<string, array<int>>
     */
    public static function journalWithEnoughDaysToResidencyProvider(): array
    {
        $c = CountryCode::any();
        $p = StayPurpose::TOURISM;
        $df = new Date('2021-01-01');

        return [
            '14d res, 14d stay' => [14, new CountryJournal([new Stay($c, $p, $df, new Date('2021-01-14'))])],
            '14d res, 102d stay' => [14, new CountryJournal([new Stay($c, $p, $df, new Date('2021-04-12'))])],
            '14d res, 365d stay' => [14, new CountryJournal([new Stay($c, $p, $df, new Date('2021-12-31'))])],
            '183d res, 183d stay' => [183, new CountryJournal([new Stay($c, $p, $df, new Date('2021-07-02'))])],
            '183d res, 184d stay' => [183, new CountryJournal([new Stay($c, $p, $df, new Date('2021-07-03'))])],
            '183d res, 256d stay' => [183, new CountryJournal([new Stay($c, $p, $df, new Date('2021-09-13'))])],
            '183d res, 365d stay' => [183, new CountryJournal([new Stay($c, $p, $df, new Date('2021-12-31'))])],
        ];
    }

    public function testMultipleStaysWithinOneYearForEnoughDaysShouldLeadToResidency(): void
    {
        $country = CountryCode::any();
        $purpose = StayPurpose::TOURISM;
        $journal = new CountryJournal([
            new Stay($country, $purpose, new Date('2021-01-01'), new Date('2021-03-31')), // 90d
            new Stay($country, $purpose, new Date('2021-06-01'), new Date('2021-06-30')), // 30d
            new Stay($country, $purpose, new Date('2021-08-01'), new Date('2021-12-31')), // 152d
        ]);
        $sut = new DaysForCalendarYearRule(183);

        $outcome = $sut->check($journal);

        $this->assertEquals([2021], $this->extractYears($outcome));
        $this->assertTrue(current($outcome)->isResident);
    }

    public function testOneStayForTwoYearsForEnoughDaysInOneOfThemShouldLeadToResidencyOnlyInIt(): void
    {
        $journal = new CountryJournal([
            new Stay(CountryCode::any(), StayPurpose::TOURISM, new Date('2020-08-01'), new Date('2021-08-01')),
        ]);
        $sut = new DaysForCalendarYearRule(183);

        $outcome = $sut->check($journal);

        $this->assertEquals([2020, 2021], $this->extractYears($outcome));
        $this->assertFalse($outcome[2020]->isResident);
        $this->assertTrue($outcome[2021]->isResident);
    }

    public function testOneStayForMultipleYearsForEnoughDaysInEachShouldLeadToResidencyInEachOfThem(): void
    {
        $journal = new CountryJournal([
            new Stay(CountryCode::any(), StayPurpose::TOURISM, new Date('2019-07-02'), new Date('2022-08-01')),
        ]);
        $sut = new DaysForCalendarYearRule(183);

        $outcome = $sut->check($journal);

        $this->assertEquals([2019, 2020, 2021, 2022], $this->extractYears($outcome));
        $this->assertTrue($outcome[2019]->isResident);
        $this->assertTrue($outcome[2020]->isResident);
        $this->assertTrue($outcome[2021]->isResident);
        $this->assertTrue($outcome[2022]->isResident);
    }

    public function testMultipleStaysForMultipleYearsForEnoughDaysInEachNotBoundaryYearsShouldLeadToResidency(): void
    {
        $country = CountryCode::any();
        $purpose = StayPurpose::TOURISM;
        $journal = new CountryJournal([
            new Stay($country, $purpose, new Date('2019-09-01'), new Date('2019-09-30')), // 30d
            new Stay($country, $purpose, new Date('2019-11-01'), new Date('2019-12-31')), // 61d
            new Stay($country, $purpose, new Date('2020-01-01'), new Date('2020-09-01')), // 245d
            new Stay($country, $purpose, new Date('2020-09-02'), new Date('2020-11-30')), // 90d
            new Stay($country, $purpose, new Date('2021-04-10'), new Date('2021-12-31')), // 266d
            new Stay($country, $purpose, new Date('2022-04-10'), new Date('2022-12-31')), // 266d
            new Stay($country, $purpose, new Date('2023-01-01'), new Date('2023-04-30')), // 120d
        ]);
        $sut = new DaysForCalendarYearRule(183);

        $outcome = $sut->check($journal);

        $this->assertEquals([2019, 2020, 2021, 2022, 2023], $this->extractYears($outcome));
        $this->assertFalse($outcome[2019]->isResident);
        $this->assertTrue($outcome[2020]->isResident);
        $this->assertTrue($outcome[2021]->isResident);
        $this->assertTrue($outcome[2022]->isResident);
        $this->assertFalse($outcome[2023]->isResident);
    }

    /**
     * @param array<YearOutcome> $outcomes
     *
     * @return array<int>
     */
    private function extractYears(array $outcomes): array
    {
        return array_values(array_map(static fn (YearOutcome $o) => $o->year->toInt(), $outcomes));
    }
}
