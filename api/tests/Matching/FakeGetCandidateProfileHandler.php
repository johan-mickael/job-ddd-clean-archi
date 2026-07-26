<?php

declare(strict_types=1);

namespace Tests\Matching;

use CandidateProfile\Application\GetCandidateProfile\CandidateProfileView;
use CandidateProfile\Application\GetCandidateProfile\GetCandidateProfile;
use CandidateProfile\Application\GetCandidateProfile\GetCandidateProfileHandlerInterface;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\Exception\CandidateProfileNotFound;

final class FakeGetCandidateProfileHandler implements GetCandidateProfileHandlerInterface
{
    public function __construct(private readonly ?CandidateProfileView $view) {}

    public function __invoke(GetCandidateProfile $query): CandidateProfileView
    {
        return $this->view ?? throw CandidateProfileNotFound::withId(new CandidateProfileId($query->candidateProfileId));
    }
}
