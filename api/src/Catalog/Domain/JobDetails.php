<?php

declare(strict_types=1);

namespace Catalog\Domain;

use Shared\Domain\RomeCode;

final readonly class JobDetails
{
    /**
     * @param list<JobSkill> $skills
     */
    public function __construct(
        public ?string $companyLogoUrl = null,
        public ?string $companyDescription = null,
        public ?WorkLocation $location = null,
        public ?RomeCode $romeCode = null,
        public ?string $romeLabel = null,
        public ?ExperienceRequirement $experienceRequirement = null,
        public bool $isWorkStudy = false,
        public ?string $salaryDescription = null,
        public int $numberOfPositions = 1,
        public array $skills = [],
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
