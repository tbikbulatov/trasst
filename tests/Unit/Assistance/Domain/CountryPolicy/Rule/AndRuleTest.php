<?php

declare(strict_types=1);

namespace App\Tests\Unit\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\CountryPolicy\Rule\AndRule;
use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\StayPurpose;
use App\Assistance\Domain\ValueObject\TaxResidencyComment;
use App\Assistance\Domain\ValueObject\Year;
use App\Assistance\Domain\ValueObject\YearOutcome;
use DateTimeImmutable as Date;
use DomainException;
use PHPUnit\Framework\TestCase;

final class AndRuleTest extends TestCase
{
    public function testConstructorThrowsExceptionWhenRulesArrayIsEmpty(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Composite rule must contain at least one rule');

        new AndRule();
    }

    public function testGetDescriptionReturnsCorrectValue(): void
    {
        $mockRule1 = $this->createMock(CountryTaxResidencyRuleInterface::class);
        $mockRule1->method('getDescription')->willReturn('Rule1 description');
        $mockRule2 = $this->createMock(CountryTaxResidencyRuleInterface::class);
        $mockRule2->method('getDescription')->willReturn('Rule2 description');
        $sut = new AndRule($mockRule1, $mockRule2);

        $result = $sut->getDescription();

        $this->assertEquals('AND composition rule: Rule1 description; Rule2 description', $result);
    }

    public function testCheckWithSingleRuleReturnsSameResult(): void
    {
        $year = new Year(2021);
        $rule = new DummyResidencyRule($year);
        $sut = new AndRule($rule);
        $journal = $this->createValidJournal();

        $result = $sut->check($journal);

        $this->assertCount(1, $result);
        $this->assertTrue($result[2021]->isResident);
        $this->assertEquals('Dummy residency rule', (string) $result[2021]->residencyComment);
    }

    public function testCheckWithMultipleResidentRulesReturnsResidentOutcome(): void
    {
        $year = new Year(2021);
        $rule1 = new DummyResidencyRule($year);
        $rule2 = new DummyResidencyRule($year);
        $sut = new AndRule($rule1, $rule2);
        $journal = $this->createValidJournal();

        $result = $sut->check($journal);

        $this->assertCount(1, $result);
        $this->assertTrue($result[2021]->isResident);
        $this->assertEquals('Dummy residency rule; Dummy residency rule', (string) $result[2021]->residencyComment);
    }

    public function testCheckWithOneNonResidentRuleReturnsNonResidentOutcome(): void
    {
        $year = new Year(2021);
        $rule1 = new DummyResidencyRule($year);
        $rule2 = new DummyNoResidencyRule($year);
        $sut = new AndRule($rule1, $rule2);
        $journal = $this->createValidJournal();

        $result = $sut->check($journal);

        $this->assertCount(1, $result);
        $this->assertFalse($result[2021]->isResident);
    }

    public function testCheckWithAllNonResidentRulesReturnsNonResidentOutcome(): void
    {
        $year = new Year(2021);
        $rule1 = new DummyNoResidencyRule($year);
        $rule2 = new DummyNoResidencyRule($year);
        $sut = new AndRule($rule1, $rule2);
        $journal = $this->createValidJournal();

        $result = $sut->check($journal);

        $this->assertCount(1, $result);
        $this->assertFalse($result[2021]->isResident);
    }

    public function testCheckWithMultipleYearsReturnsCorrectOutcomes(): void
    {
        $year2020 = new Year(2020);
        $year2021 = new Year(2021);
        $year2022 = new Year(2022);

        $rule1 = new DummyResidencyRule($year2020);
        $rule2 = new DummyResidencyRule($year2021);
        $rule3 = new DummyNoResidencyRule($year2021);
        $rule4 = new DummyResidencyRule($year2022);

        $sut = new AndRule($rule1, $rule2, $rule3, $rule4);
        $journal = $this->createValidJournal();

        $result = $sut->check($journal);

        $this->assertCount(3, $result);
        $this->assertTrue($result[2020]->isResident);
        $this->assertFalse($result[2021]->isResident);
        $this->assertTrue($result[2022]->isResident);
    }

    public function testCheckWithMockRules(): void
    {
        $journal = $this->createValidJournal();
        $year = new Year(2021);

        $rule1 = $this->createMock(CountryTaxResidencyRuleInterface::class);
        $rule1->method('check')->willReturn([
            2021 => YearOutcome::resident($year, TaxResidencyComment::single('Rule 1')),
        ]);

        $rule2 = $this->createMock(CountryTaxResidencyRuleInterface::class);
        $rule2->method('check')->willReturn([
            2021 => YearOutcome::resident($year, TaxResidencyComment::single('Rule 2')),
        ]);

        $sut = new AndRule($rule1, $rule2);

        $result = $sut->check($journal);

        $this->assertCount(1, $result);
        $this->assertTrue($result[2021]->isResident);
        $this->assertEquals('Rule 1; Rule 2', (string) $result[2021]->residencyComment);
    }

    private function createValidJournal(): CountryJournal
    {
        $country = CountryCode::any();
        $purpose = StayPurpose::any();
        $stay = new Stay($country, $purpose, new Date('2021-01-01'), new Date('2021-01-10'));

        return new CountryJournal([$stay]);
    }
}
