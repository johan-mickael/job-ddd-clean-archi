<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

use CandidateProfile\Domain\Exception\CvProfileExtractionFailed;

interface CvProfileExtractorInterface
{
    /**
     * Returns the LLM's raw decoded JSON - untrusted until validated and
     * mapped into value objects at the Application boundary.
     *
     * @return array<string, mixed>
     *
     * @throws CvProfileExtractionFailed
     */
    public function extract(string $cvText): array;
}
