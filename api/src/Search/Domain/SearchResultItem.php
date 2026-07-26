<?php

declare(strict_types=1);

namespace Search\Domain;

final readonly class SearchResultItem
{
    public function __construct(
        public string $jobId,
        public string $title,
        public string $companyName,
        public ?string $locationLabel,
        public string $applyUrl,
        public \DateTimeImmutable $publishedAt,
        public float $score,
    ) {}
}
