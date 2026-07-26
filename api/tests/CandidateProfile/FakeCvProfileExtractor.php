<?php

declare(strict_types=1);

namespace Tests\CandidateProfile;

use CandidateProfile\Domain\CvProfileExtractorInterface;

final class FakeCvProfileExtractor implements CvProfileExtractorInterface
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        private readonly array $response = [
            'seniority' => 'senior',
            'domains' => ['backend'],
            'roles' => ['Backend Developer'],
            'languages' => [['name' => 'French', 'proficiency' => 'native']],
            'skills' => [['label' => 'PHP', 'yearsOfExperience' => 5]],
        ],
    ) {}

    public function extract(string $cvText): array
    {
        return $this->response;
    }
}
