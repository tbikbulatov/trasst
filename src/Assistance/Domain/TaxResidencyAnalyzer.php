<?php

declare(strict_types=1);

namespace App\Assistance\Domain;

use App\Assistance\Domain\CountryPolicy\CountryTaxResidencyPoliciesRegistryInterface;
use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\ValueObject\AnalysisOutcome;
use App\Assistance\Domain\ValueObject\CountryOutcome;
use App\Assistance\Domain\ValueObject\GroupingOperator;
use App\Assistance\Domain\ValueObject\YearOutcome;

final readonly class TaxResidencyAnalyzer
{
    public function __construct(
        private CountryTaxResidencyPoliciesRegistryInterface $policiesRegistry,
    ) {
    }

    public function analyze(Journal $journal): AnalysisOutcome
    {
        $countriesOutcomes = [];
        foreach ($journal->splitByCountries() as $countryJournal) {
            if (!$this->policiesRegistry->has($countryJournal->country)) {
                $countriesOutcomes[] = new CountryOutcome($countryJournal->country, []);
                continue;
            }

            $rulesOutcomes = [];
            foreach ($this->policiesRegistry->get($countryJournal->country) as $rule) {
                /** @var CountryTaxResidencyRuleInterface $rule */
                $rulesOutcomes[] = $rule->check($countryJournal);
            }

            $yearOutcomes = YearOutcome::groupByYear(array_merge(...$rulesOutcomes), GroupingOperator::OR);
            $countriesOutcomes[] = new CountryOutcome($countryJournal->country, $yearOutcomes);
        }

        return new AnalysisOutcome($countriesOutcomes);
    }
}
