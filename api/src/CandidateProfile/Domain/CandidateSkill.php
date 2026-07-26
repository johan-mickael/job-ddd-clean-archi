<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

use CandidateProfile\Domain\Exception\InvalidCvProfileData;

final readonly class CandidateSkill
{
    public function __construct(
        public string $label,
        public ?int $yearsOfExperience = null,
    ) {
        if (\trim($this->label) === '') {
            throw InvalidCvProfileData::becauseOfField('skills[].label', 'must not be blank.');
        }

        if ($this->yearsOfExperience !== null && $this->yearsOfExperience < 0) {
            throw InvalidCvProfileData::becauseOfField('skills[].yearsOfExperience', 'must not be negative.');
        }
    }
}
