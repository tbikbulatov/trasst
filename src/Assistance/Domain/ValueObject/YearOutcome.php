<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

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

    public static function emptyForYear(Year $year): self
    {
        return new self($year, false);
    }

    public function sumUp(self $yearOutcome): self
    {
        assert($this->year->equals($yearOutcome->year), 'Years does not match');

        $isResident = $this->isResident || $yearOutcome->isResident;

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
