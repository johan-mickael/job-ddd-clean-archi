<?php

declare(strict_types=1);

namespace CandidateProfile\Application\GetCandidateProfile;

use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateProfileRepositoryInterface;
use CandidateProfile\Domain\Exception\CandidateProfileNotFound;

final class GetCandidateProfileHandler implements GetCandidateProfileHandlerInterface
{
    public function __construct(
        private readonly CandidateProfileRepositoryInterface $profiles,
        private readonly CandidateProfileViewMapper $viewMapper,
    ) {}

    public function __invoke(GetCandidateProfile $query): CandidateProfileView
    {
        $id = new CandidateProfileId($query->candidateProfileId);
        $profile = $this->profiles->ofId($id);

        // Someone else's profile doesn't exist as far as this requester is
        // concerned - a 403 would confirm the id is real, a 404 doesn't.
        if ($profile->ownerId !== $query->requesterId) {
            throw CandidateProfileNotFound::withId($id);
        }

        return $this->viewMapper->toView($profile);
    }
}
