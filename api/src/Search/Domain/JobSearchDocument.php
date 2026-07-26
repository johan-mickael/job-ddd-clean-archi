<?php

declare(strict_types=1);

namespace Search\Domain;

final readonly class JobSearchDocument
{
    /**
     * @param list<string>    $skillLabels
     * @param ?list<float>    $embedding
     */
    public function __construct(
        public string $jobId,
        public string $title,
        public string $description,
        public string $companyName,
        public string $contractType,
        public string $applyUrl,
        public \DateTimeImmutable $publishedAt,
        public ?string $locationLabel,
        public ?string $romeCode,
        public ?string $romeLabel,
        public ?string $salaryDescription,
        public bool $isWorkStudy,
        public array $skillLabels,
        public ?\DateTimeImmutable $updatedAt,
        public ?array $embedding,
    ) {}
}
