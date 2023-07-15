<?php

declare(strict_types=1);

namespace App\Assistance\Domain;

use App\Assistance\Domain\CountryPolicy\CountryTaxResidencyPoliciesRegistryInterface;
use App\Assistance\Domain\CountryPolicy\Rule\CountryTaxResidencyRuleInterface;
use App\Assistance\Domain\Entity\Journal;
use App\Assistance\Domain\ValueObject\AnalysisOutcome;
use App\Assistance\Domain\ValueObject\CountryOutcome;

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
                /* @var CountryTaxResidencyRuleInterface $rule */
                $rulesOutcomes[] = $rule->check($countryJournal);
            }

            $countriesOutcomes[] = new CountryOutcome($countryJournal->country, array_merge(...$rulesOutcomes));
        }

        return new AnalysisOutcome($countriesOutcomes);
    }
}
