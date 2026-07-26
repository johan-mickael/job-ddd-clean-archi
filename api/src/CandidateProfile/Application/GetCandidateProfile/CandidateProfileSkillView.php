<?php

declare(strict_types=1);

namespace CandidateProfile\Application\GetCandidateProfile;

final readonly class CandidateProfileSkillView
{
    public function __construct(
        public string $label,
        public ?int $yearsOfExperience,
    ) {}
}
