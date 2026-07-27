<?php

declare(strict_types=1);

namespace CandidateProfile\Application\ListMyCandidateProfiles;

use CandidateProfile\Application\GetCandidateProfile\CandidateProfileView;

interface ListMyCandidateProfilesHandlerInterface
{
    /**
     * @return list<CandidateProfileView>
     */
    public function __invoke(ListMyCandidateProfiles $query): array;
}
