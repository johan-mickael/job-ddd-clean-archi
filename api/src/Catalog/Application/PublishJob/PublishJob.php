<?php

declare(strict_types=1);

namespace Catalog\Application\PublishJob;

final readonly class PublishJob
{
    /**
     * @param list<PublishJobSkill> $skills
     */
    public function __construct(
        public string $jobId,
        public string $title,
        public string $description,
        public string $companyName,
        public string $contractType,
        public string $applyUrl,
        public \DateTimeImmutable $publishedAt,
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
        public ?int $numberOfPositions = null,
        public array $skills = [],
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }
}
