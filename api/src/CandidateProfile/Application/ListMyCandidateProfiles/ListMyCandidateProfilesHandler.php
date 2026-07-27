<?php

declare(strict_types=1);

namespace CandidateProfile\Application\ListMyCandidateProfiles;

use CandidateProfile\Application\GetCandidateProfile\CandidateProfileViewMapper;
use CandidateProfile\Domain\CandidateProfileRepositoryInterface;

final class ListMyCandidateProfilesHandler implements ListMyCandidateProfilesHandlerInterface
{
    public function __construct(
        private readonly CandidateProfileRepositoryInterface $profiles,
        private readonly CandidateProfileViewMapper $viewMapper,
    ) {}

    public function __invoke(ListMyCandidateProfiles $query): array
    {
        return \array_map(
            $this->viewMapper->toView(...),
            $this->profiles->ofOwnerId($query->requesterId),
        );
    }
}
