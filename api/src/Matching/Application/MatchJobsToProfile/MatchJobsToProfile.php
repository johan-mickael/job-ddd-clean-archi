<?php

declare(strict_types=1);

namespace Matching\Application\MatchJobsToProfile;

final readonly class MatchJobsToProfile
{
    public function __construct(
        public string $candidateProfileId,
        public int $page = 1,
        public int $pageSize = 20,
    ) {}
}
