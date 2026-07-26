<?php

declare(strict_types=1);

namespace Catalog\Application\PublishJob;

final readonly class PublishJob
{
    public function __construct(
        public string $jobId,
        public string $title,
        public string $description,
        public string $companyName,
        public string $contractType,
        public string $applyUrl,
        public \DateTimeImmutable $publishedAt,
    ) {
    }
}
