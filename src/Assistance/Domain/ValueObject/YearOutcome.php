<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use DomainException;

final readonly class YearOutcome
{
    private function __construct(
        public Year $year,
        public bool $isResident,
        public ?TaxResidencyComment $residencyComment = null,
        public ?bool $canBecomeResident = null,
        public ?TaxResidencyComment $potentialResidencyComment = null,
    ) {
    }

    public static function resident(Year $year, TaxResidencyComment $residencyComment): self
    {
        return new self($year, true, $residencyComment);
    }

    public static function notResident(Year $year): self
    {
        return new self($year, false);
    }

    /**
     * @param array<YearOutcome> $yearsOutcomes
     *
     * @return array<int, YearOutcome> Indexed by years
     */
    public static function groupByYear(array $yearsOutcomes, GroupingOperator $condition): array
    {
        /** @var array<int, YearOutcome> $grouped */
        $grouped = [];
        foreach ($yearsOutcomes as $outcome) {
            $year = $outcome->year->toInt();

            $grouped[$year] ??= self::createDummy($outcome->year, $condition);
            $grouped[$year] = $grouped[$year]->summarize($outcome, $condition);
        }

        return $grouped;
    }

    private static function createDummy(Year $year, GroupingOperator $condition): self
    {
        return match ($condition) {
            GroupingOperator::AND => self::resident($year, TaxResidencyComment::single('')),
            GroupingOperator::OR => self::notResident($year),
        };
    }

    private function summarize(self $yearOutcome, GroupingOperator $condition): self
    {
        $this->year->equals($yearOutcome->year) ?: throw new DomainException('Years does not match');

        $isResident = match ($condition) {
            GroupingOperator::AND => $this->isResident && $yearOutcome->isResident,
            GroupingOperator::OR => $this->isResident || $yearOutcome->isResident,
        };

        return new self($this->year, $isResident, $this->concatComments($yearOutcome->residencyComment));
    }

    private function concatComments(?TaxResidencyComment $comment): ?TaxResidencyComment
    {
        if (!$this->residencyComment && !$comment) {
            return null;
        }

        if ($this->residencyComment && $comment) {
            return $this->residencyComment->concat($comment);
        }

        return $this->residencyComment ?? $comment;
    }
}
