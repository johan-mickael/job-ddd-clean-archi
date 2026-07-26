<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

use CandidateProfile\Domain\Exception\CandidateProfileNotFound;

interface CandidateProfileRepositoryInterface
{
    /**
     * @throws CandidateProfileNotFound
     */
    public function ofId(CandidateProfileId $id): CandidateProfile;

    public function add(CandidateProfile $profile): void;
}
