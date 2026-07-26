<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

use CandidateProfile\Domain\Exception\CvTextNotExtractable;

interface TextExtractorInterface
{
    /**
     * @throws CvTextNotExtractable
     */
    public function extract(string $fileContent, string $fileName): string;
}
