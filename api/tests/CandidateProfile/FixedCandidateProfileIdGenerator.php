<?php

declare(strict_types=1);

namespace Tests\CandidateProfile;

use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateProfileIdGeneratorInterface;

final class FixedCandidateProfileIdGenerator implements CandidateProfileIdGeneratorInterface
{
    public function __construct(private readonly CandidateProfileId $id) {}

    public function next(): CandidateProfileId
    {
        return $this->id;
    }
}
