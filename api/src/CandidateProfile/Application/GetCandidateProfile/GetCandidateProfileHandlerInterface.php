<?php

declare(strict_types=1);

namespace CandidateProfile\Application\GetCandidateProfile;

interface GetCandidateProfileHandlerInterface
{
    public function __invoke(GetCandidateProfile $query): CandidateProfileView;
}
