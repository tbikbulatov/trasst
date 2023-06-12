<?php

declare(strict_types=1);

namespace App\Tests\Assistance\Domain;

use App\Assistance\Domain\CountryPolicy\CountryTaxResidencyPoliciesRegistryInterface;
use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\TaxResidencyAnalyzer;
use App\Assistance\Domain\ValueObject\CountryCode;
use App\Assistance\Domain\ValueObject\Stay;
use App\Assistance\Domain\ValueObject\StayPurpose;
use App\Assistance\Domain\ValueObject\Year;
use App\Assistance\Infrastructure\IdGenerator\JournalIdGenerator;
use App\Tests\Assistance\Domain\CountryPolicy\Rule\DummyNoResidencyRule;
use App\Tests\Assistance\Domain\CountryPolicy\Rule\DummyResidencyRule;
use App\Tests\Assistance\Domain\ValueObject\CountryTaxPolicyMother;
use App\Tests\Common\BaseKernelTestCase;
use DateTimeImmutable as Date;
use Generator;

final class TaxResidencyAnalyzerTest extends BaseKernelTestCase
{
    /**
     * @param array<string, array<int>> $expectations
     * @dataProvider analysisOutcomeProvider
     */
    public function testAnalysisOutcomeShouldMatchCountryAndYearOutcomesAmount(
        array $expectations,
        Journal $journal,
    ): void {
        $sut = self::get(TaxResidencyAnalyzer::class);

        $outcome = $sut->execute($journal);

        $this->assertCount(count($expectations), $outcome);
        foreach ($outcome as $countryOutcome) {
            $this->assertContains($countryOutcome->country->value, array_keys($expectations));
            $this->assertSame($expectations[$countryOutcome->country->value], array_keys($countryOutcome->yearsOutcomes));
        }
    }

    /**
     * @return Generator<string,array>
     */
    private function analysisOutcomeProvider(): Generator
    {
        $purpose = StayPurpose::TOURISM;
        $country1 = CountryCode::ARMENIA;
        $country2 = CountryCode::GEORGIA;
        $country3 = CountryCode::RUSSIA;
        $country4 = CountryCode::TURKEY;

        yield 'within one year in one country' => [
            [
                $country1->value => [2022],
            ],
            new Journal(JournalIdGenerator::generate(), [
                new Stay($country1, $purpose, new Date('2022-01-01'), new Date('2022-03-31')),
            ])
        ];
        yield 'within one year in a few country' => [
            [
                $country1->value => [2022],
                $country2->value => [2022],
                $country3->value => [2022],
                $country4->value => [2022],
            ],
            new Journal(JournalIdGenerator::generate(), [
                new Stay($country1, $purpose, new Date('2022-01-01'), new Date('2022-03-31')),
                new Stay($country2, $purpose, new Date('2022-04-01'), new Date('2022-06-30')),
                new Stay($country3, $purpose, new Date('2022-07-01'), new Date('2022-09-30')),
                new Stay($country4, $purpose, new Date('2022-10-01'), new Date('2022-12-31')),
            ])
        ];
        yield 'within few years in one country' => [
            [
                $country1->value => [2021, 2022, 2023],
            ],
            new Journal(JournalIdGenerator::generate(), [
                new Stay($country1, $purpose, new Date('2021-01-01'), new Date('2023-12-31')),
            ])
        ];
        yield 'within few years in one country, edge dates' => [
            [
                $country1->value => [2021, 2022, 2023],
            ],
            new Journal(JournalIdGenerator::generate(), [
                new Stay($country1, $purpose, new Date('2021-12-31'), new Date('2023-01-01'))
            ])
        ];
        yield 'within few years in a few country' => [
            [
                $country1->value => [2021, 2022, 2023],
                $country2->value => [2021, 2022, 2023],
                $country3->value => [2021, 2022, 2023],
                $country4->value => [2021, 2022, 2023],
            ],
            new Journal(JournalIdGenerator::generate(), [
                new Stay($country1, $purpose, new Date('2021-01-01'), new Date('2021-03-31')),
                new Stay($country1, $purpose, new Date('2022-01-01'), new Date('2022-03-31')),
                new Stay($country1, $purpose, new Date('2023-01-01'), new Date('2023-03-31')),
                new Stay($country2, $purpose, new Date('2021-04-01'), new Date('2021-06-30')),
                new Stay($country2, $purpose, new Date('2022-04-01'), new Date('2022-06-30')),
                new Stay($country2, $purpose, new Date('2023-04-01'), new Date('2023-06-30')),
                new Stay($country3, $purpose, new Date('2021-07-01'), new Date('2021-09-30')),
                new Stay($country3, $purpose, new Date('2022-07-01'), new Date('2022-09-30')),
                new Stay($country3, $purpose, new Date('2023-07-01'), new Date('2023-09-30')),
                new Stay($country4, $purpose, new Date('2021-10-01'), new Date('2021-12-31')),
                new Stay($country4, $purpose, new Date('2022-10-01'), new Date('2022-12-31')),
                new Stay($country4, $purpose, new Date('2023-10-01'), new Date('2023-12-31')),
            ])
        ];
        yield 'within few years in a few country, different years' => [
            [
                $country1->value => [2021, 2022],
                $country2->value => [2020],
                $country3->value => [2017, 2018, 2019],
                $country4->value => [2019, 2021, 2022, 2023],
            ],
            new Journal(JournalIdGenerator::generate(), [
                new Stay($country1, $purpose, new Date('2021-01-01'), new Date('2021-03-31')),
                new Stay($country1, $purpose, new Date('2022-01-01'), new Date('2022-03-31')),
                new Stay($country2, $purpose, new Date('2020-04-01'), new Date('2020-06-30')),
                new Stay($country3, $purpose, new Date('2017-12-31'), new Date('2019-01-01')),
                new Stay($country4, $purpose, new Date('2019-10-01'), new Date('2019-12-31')),
                new Stay($country4, $purpose, new Date('2021-10-01'), new Date('2021-12-31')),
                new Stay($country4, $purpose, new Date('2022-10-01'), new Date('2022-12-31')),
                new Stay($country4, $purpose, new Date('2023-10-01'), new Date('2023-12-31')),
            ])
        ];
    }

    /**
     * @dataProvider rulesToCheckCallsProvider
     */
    public function testEachCountryPolicyRuleIsChecked(array $rules): void
    {
        $countryTaxPolicy = CountryTaxPolicyMother::create($rules);
        $country = CountryCode::from($countryTaxPolicy::getCountryCode());
        $purpose = StayPurpose::TOURISM;
        $journal = new Journal(JournalIdGenerator::generate(), [
            new Stay($country, $purpose, new Date('2022-01-01'), new Date('2022-01-31')),
        ]);
        $policiesRegistry = $this->createStub(CountryTaxResidencyPoliciesRegistryInterface::class);
        $policiesRegistry->method('has')->willReturn(true);
        $policiesRegistry->method('get')->willReturn($countryTaxPolicy);
        $sut = new TaxResidencyAnalyzer($policiesRegistry);

        $outcome = $sut->execute($journal);

        $this->assertCount(1, $outcome);
    }

    /**
     * @return Generator<string,array<CountryTaxResidencyRuleInterface>>
     */
    private function rulesToCheckCallsProvider(): Generator
    {
        yield 'one rule' => [$this->generateRuleMocks(1)];
        yield 'two rules' => [$this->generateRuleMocks(2)];
        yield 'five rules' => [$this->generateRuleMocks(5)];
        yield 'ten rules' => [$this->generateRuleMocks(10)];
    }

    private function generateRuleMocks(int $amount): array
    {
        $acc = [];
        while ($amount--) {
            $rule = $this->createMock(CountryTaxResidencyRuleInterface::class);
            $rule->expects($this->once())->method('check');
            $acc[] = $rule;
        }

        return $acc;
    }

    /**
     * @dataProvider dummyRulesToCheckOutcomesSumUpProvider
     */
    public function testItSumUpRuleOutcomesCorrectly(callable $getRules): void
    {
        $year = 2022;
        [$expectResidency, $rules] = array_values($getRules(Year::fromInt($year)));

        $countryTaxPolicy = CountryTaxPolicyMother::create($rules);
        $country = CountryCode::from($countryTaxPolicy::getCountryCode());
        $purpose = StayPurpose::TOURISM;
        $journal = new Journal(JournalIdGenerator::generate(), [
            new Stay($country, $purpose, new Date("$year-01-01"), new Date("$year-12-31")),
        ]);
        $policiesRegistry = $this->createStub(CountryTaxResidencyPoliciesRegistryInterface::class);
        $policiesRegistry->method('has')->willReturn(true);
        $policiesRegistry->method('get')->willReturn($countryTaxPolicy);
        $sut = new TaxResidencyAnalyzer($policiesRegistry);

        $outcome = $sut->execute($journal);

        $this->assertCount(1, $outcome, 'One country outcome expected');
        $this->assertCount(1, $outcome->current()->yearsOutcomes, 'One year outcome expected');
        $this->assertNotEmpty($outcome->current()->yearsOutcomes[$year]);
        $this->assertEquals($expectResidency, $outcome->current()->yearsOutcomes[$year]->isResident);
    }

    /**
     * @return Generator<string,array>
     */
    private function dummyRulesToCheckOutcomesSumUpProvider(): Generator
    {
        yield 'one residency rule' => [fn (Year $year) => [
            'expect' => true,
            'rules' => [new DummyResidencyRule($year)],
        ]];
        yield 'one no-residency rule' => [fn (Year $year) => [
            'expect' => false,
            'rules' => [new DummyNoResidencyRule($year)],
        ]];
        yield 'residency ruleset' => [fn (Year $year) => [
            'expect' => true,
            'rules' => [
                new DummyResidencyRule($year),
                new DummyResidencyRule($year),
                new DummyResidencyRule($year),
                new DummyResidencyRule($year),
            ],
        ]];
        yield 'no-residency ruleset' => [fn (Year $year) => [
            'expect' => false,
            'rules' => [
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
            ],
        ]];
        yield 'first of ruleset makes residency' => [fn (Year $year) => [
            'expect' => true,
            'rules' => [
                new DummyResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
            ],
        ]];
        yield 'last of ruleset makes residency' => [fn (Year $year) => [
            'expect' => true,
            'rules' => [
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyResidencyRule($year),
            ],
        ]];
        yield 'mean of ruleset makes residency' => [fn (Year $year) => [
            'expect' => true,
            'rules' => [
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
            ],
        ]];
        yield 'few residency rules of ruleset makes residency' => [fn (Year $year) => [
            'expect' => true,
            'rules' => [
                new DummyNoResidencyRule($year),
                new DummyResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyNoResidencyRule($year),
                new DummyResidencyRule($year),
                new DummyNoResidencyRule($year),
            ],
        ]];
    }
}
