<?php

declare(strict_types=1);

namespace CandidateProfile\Application\ListMyCandidateProfiles;

final readonly class ListMyCandidateProfiles
{
    public function __construct(public string $requesterId) {}
}
