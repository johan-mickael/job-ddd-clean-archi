<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

use CandidateProfile\Domain\Exception\InvalidCandidateProfileId;

final readonly class CandidateProfileId
{
    public function __construct(public string $value)
    {
        if (\trim($value) === '') {
            throw InvalidCandidateProfileId::blank();
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
