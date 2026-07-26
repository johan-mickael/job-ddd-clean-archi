<?php

declare(strict_types=1);

namespace Catalog\Application\GetJob;

final readonly class JobView
{
    /**
     * @param list<JobViewSkill> $skills
     */
    public function __construct(
        public string $jobId,
        public string $title,
        public string $description,
        public string $companyName,
        public string $contractType,
        public string $applyUrl,
        public \DateTimeImmutable $publishedAt,
        public string $status,
        public ?string $companyLogoUrl = null,
        public ?string $companyDescription = null,
        public ?string $locationLabel = null,
        public ?string $locationInseeCode = null,
        public ?string $locationPostalCode = null,
        public ?string $romeCode = null,
        public ?string $romeLabel = null,
        public ?string $experienceRequirement = null,
        public bool $isWorkStudy = false,
        public ?string $salaryDescription = null,
        public int $numberOfPositions = 1,
        public array $skills = [],
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
