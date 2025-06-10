<?php

declare(strict_types=1);

namespace App\Tests\Unit\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\CountryPolicy\Rule\DaysForLastSequentialYearsInProportionRule;
use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\DaysOfYearRatio;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\StayPurpose;
use App\Assistance\Domain\ValueObject\YearOutcome;
use DateTimeImmutable as Date;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DaysForLastSequentialYearsInProportionRuleTest extends TestCase
{
    #[DataProvider('validDaysAmountForRuleConfigurationProvider')]
    public function testAmountOfDaysToResidencyShouldBePositive(int $days): void
    {
        $sut = new DaysForLastSequentialYearsInProportionRule($days, new DaysOfYearRatio(1));

        self::assertNotEmpty($sut);
    }

    public static function validDaysAmountForRuleConfigurationProvider(): array
    {
        return [
            '1 day' => [1],
            '10 days' => [10],
            '100 days' => [100],
            '365 days' => [365],
            '1000 days' => [1000],
        ];
    }

    #[DataProvider('invalidDaysAmountForRuleConfigurationProvider')]
    public function testItShouldThrowExceptionWhenSetupInvalidAmountOfDaysToResidency(int $days): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Value must be positive');

        new DaysForLastSequentialYearsInProportionRule($days, new DaysOfYearRatio(1));
    }

    public static function invalidDaysAmountForRuleConfigurationProvider(): array
    {
        return [
            '-10 days' => [-10],
            '-1 day' => [-1],
            '0 days' => [0],
        ];
    }

    public function testGetDescriptionReturnsCorrectValue(): void
    {
        $sut = new DaysForLastSequentialYearsInProportionRule(183, new DaysOfYearRatio(1));

        $result = $sut->getDescription();

        $this->assertEquals('Stay for 183 days for current and past years (in proportion)', $result);
    }

    public function testSingleYearStayWithLessDaysThanRequiredShouldNotLeadToResidency(): void
    {
        $journal = new CountryJournal([
            new Stay(CountryCode::any(), StayPurpose::any(), new Date('2021-01-01'), new Date('2021-07-01')), // 182 days
        ]);
        $sut = new DaysForLastSequentialYearsInProportionRule(183, new DaysOfYearRatio(1));

        $outcome = $sut->check($journal);

        $this->assertEquals([2021], $this->extractYears($outcome));
        $this->assertFalse($outcome[2021]->isResident);
    }

    public function testSingleYearStayWithExactDaysRequiredShouldLeadToResidency(): void
    {
        $journal = new CountryJournal([
            new Stay(CountryCode::any(), StayPurpose::any(), new Date('2021-01-01'), new Date('2021-07-02')), // 183 days
        ]);
        $sut = new DaysForLastSequentialYearsInProportionRule(183, new DaysOfYearRatio(1));

        $outcome = $sut->check($journal);

        $this->assertEquals([2021], $this->extractYears($outcome));
        $this->assertTrue($outcome[2021]->isResident);
    }

    public function testSingleYearStayWithMoreDaysThanRequiredShouldLeadToResidency(): void
    {
        $journal = new CountryJournal([
            new Stay(CountryCode::any(), StayPurpose::any(), new Date('2021-01-01'), new Date('2021-12-31')), // 365 days
        ]);
        $sut = new DaysForLastSequentialYearsInProportionRule(183, new DaysOfYearRatio(1));

        $outcome = $sut->check($journal);

        $this->assertEquals([2021], $this->extractYears($outcome));
        $this->assertTrue($outcome[2021]->isResident);
    }

    public function testMultipleYearsWithProportionRatioShouldCalculateCorrectly(): void
    {
        $country = CountryCode::any();
        $purpose = StayPurpose::any();
        $journal = new CountryJournal([
            // 2020: 100 days
            new Stay($country, $purpose, new Date('2020-01-01'), new Date('2020-04-09')),
            // 2021: 150 days
            new Stay($country, $purpose, new Date('2021-01-01'), new Date('2021-05-30')),
        ]);

        // 150 + (100 * 1/3) = 150 + 33.33 = 183.33 days (rounded up to 184)
        $sut = new DaysForLastSequentialYearsInProportionRule(
            183,
            new DaysOfYearRatio(1), // Current year ratio
            new DaysOfYearRatio(1, 3), // Previous year ratio (1/3)
        );

        $outcome = $sut->check($journal);

        $this->assertEquals([2020, 2021], $this->extractYears($outcome));
        $this->assertFalse($outcome[2020]->isResident); // 2020 alone doesn't have enough days
        $this->assertTrue($outcome[2021]->isResident); // 2021 with weighted 2020 days has enough
    }

    public function testMultipleYearsWithProportionRatioNotEnoughDaysShouldNotLeadToResidency(): void
    {
        $country = CountryCode::any();
        $purpose = StayPurpose::any();
        $journal = new CountryJournal([
            // 2020: 90 days
            new Stay($country, $purpose, new Date('2020-01-01'), new Date('2020-03-31')),
            // 2021: 150 days
            new Stay($country, $purpose, new Date('2021-01-01'), new Date('2021-05-30')),
        ]);

        // 150 + (90 * 1/3) = 150 + 30 = 180 days (not enough for 183)
        $sut = new DaysForLastSequentialYearsInProportionRule(
            183,
            new DaysOfYearRatio(1), // Current year ratio
            new DaysOfYearRatio(1, 3), // Previous year ratio (1/3)
        );

        $outcome = $sut->check($journal);

        $this->assertEquals([2020, 2021], $this->extractYears($outcome));
        $this->assertFalse($outcome[2020]->isResident);
        $this->assertFalse($outcome[2021]->isResident);
    }

    public function testMultipleYearsWithComplexProportionRatio(): void
    {
        $country = CountryCode::any();
        $purpose = StayPurpose::any();
        $journal = new CountryJournal([
            // 2019: 60 days
            new Stay($country, $purpose, new Date('2019-01-01'), new Date('2019-03-01')),
            // 2020: 90 days
            new Stay($country, $purpose, new Date('2020-01-01'), new Date('2020-03-31')),
            // 2021: 120 days
            new Stay($country, $purpose, new Date('2021-01-01'), new Date('2021-04-30')),
        ]);

        // For 2021: 120 + (90 * 1/3) + (60 * 1/6) = 120 + 30 + 10 = 160 days (not enough for 183)
        // For 2020: 90 + (60 * 1/3) = 90 + 20 = 110 days (not enough for 183)
        $sut = new DaysForLastSequentialYearsInProportionRule(
            183,
            new DaysOfYearRatio(1), // Current year ratio
            new DaysOfYearRatio(1, 3), // Previous year ratio (1/3)
            new DaysOfYearRatio(1, 6), // Year before previous ratio (1/6)
        );

        $outcome = $sut->check($journal);

        $this->assertEquals([2019, 2020, 2021], $this->extractYears($outcome));
        $this->assertFalse($outcome[2019]->isResident);
        $this->assertFalse($outcome[2020]->isResident);
        $this->assertFalse($outcome[2021]->isResident);

        // Now let's try with a lower threshold
        $sut = new DaysForLastSequentialYearsInProportionRule(
            150,
            new DaysOfYearRatio(1), // Current year ratio
            new DaysOfYearRatio(1, 3), // Previous year ratio (1/3)
            new DaysOfYearRatio(1, 6), // Year before previous ratio (1/6)
        );

        $outcome = $sut->check($journal);

        $this->assertFalse($outcome[2019]->isResident);
        $this->assertFalse($outcome[2020]->isResident);
        $this->assertTrue($outcome[2021]->isResident); // Now 2021 has enough days with the weighted sum
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
