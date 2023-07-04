<?php

declare(strict_types=1);

namespace App\Assistance\Domain\ValueObject;

use InvalidArgumentException;

final readonly class YearOutcome
{
    public function __construct(
        public Year $year,
        public bool $isResident,
        public ?TaxResidencyComment $residencyComment = null,
        public ?bool $canBecomeResident = null,
        public ?TaxResidencyComment $potentialResidencyComment = null,
    ) {}

    public static function emptyForYear(Year $year): self
    {
        return new self($year, false);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function sumUp(self $yearOutcome): self
    {
        $this->year->equals($yearOutcome->year) ?? throw new InvalidArgumentException('Years does not match');

        return new self(
            $this->year,
            $this->isResident || $yearOutcome->isResident,
            $yearOutcome->isResident ? $this->concatComments($yearOutcome->residencyComment) : $this->residencyComment,
        );
    }

    private function concatComments(TaxResidencyComment $comment): TaxResidencyComment
    {
        return $this->residencyComment ? $this->residencyComment->concat($comment) : $comment;
    }
}
