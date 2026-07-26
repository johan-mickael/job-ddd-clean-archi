<?php

declare(strict_types=1);

namespace CandidateProfile\Application\UploadCv;

final readonly class UploadCv
{
    public function __construct(
        public string $fileContent,
        public string $fileName,
    ) {}
}
