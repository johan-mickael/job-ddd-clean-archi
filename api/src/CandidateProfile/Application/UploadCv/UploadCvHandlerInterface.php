<?php

declare(strict_types=1);

namespace CandidateProfile\Application\UploadCv;

use CandidateProfile\Domain\CandidateProfileId;

interface UploadCvHandlerInterface
{
    public function __invoke(UploadCv $command): CandidateProfileId;
}
