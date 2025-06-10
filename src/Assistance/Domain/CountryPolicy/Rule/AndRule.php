<?php

declare(strict_types=1);

namespace App\Assistance\Domain\CountryPolicy\Rule;

use App\Assistance\Domain\ValueObject\CountryJournal;
use App\Assistance\Domain\ValueObject\GroupingOperator;
use App\Assistance\Domain\ValueObject\YearOutcome;
use DomainException;
use Override;

final readonly class AndRule implements CountryTaxResidencyRuleInterface
{
    /** @var CountryTaxResidencyRuleInterface[] */
    private array $rules;

    private string $description;

    /**
     * @throws DomainException
     */
    public function __construct(CountryTaxResidencyRuleInterface ...$rules)
    {
        $this->rules = $rules ?: throw new DomainException('Composite rule must contain at least one rule');

        $this->description = 'AND composition rule: '.implode('; ',
            array_map(fn (CountryTaxResidencyRuleInterface $rule) => $rule->getDescription(), $rules),
        );
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function check(CountryJournal $journal): array
    {
        $outcomes = [];
        foreach ($this->rules as $rule) {
            $outcomes[] = $rule->check($journal);
        }

        return YearOutcome::groupByYear(array_merge(...$outcomes), GroupingOperator::AND);
    }
}
