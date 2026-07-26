<?php

declare(strict_types=1);

namespace CandidateProfile\Application\GetCandidateProfile;

final readonly class CandidateProfileLanguageView
{
    public function __construct(
        public string $name,
        public string $proficiency,
    ) {}
}
