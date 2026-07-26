<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

interface CandidateProfileIdGeneratorInterface
{
    public function next(): CandidateProfileId;
}
