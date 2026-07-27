<?php

declare(strict_types=1);

namespace CandidateProfile\Application\GetCandidateProfile;

final readonly class GetCandidateProfile
{
    public function __construct(
        public string $candidateProfileId,
        public string $requesterId,
    ) {}
}
