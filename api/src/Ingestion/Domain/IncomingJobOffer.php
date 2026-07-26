<?php

declare(strict_types=1);

namespace Ingestion\Domain;

final readonly class IncomingJobOffer
{
    public function __construct(
        public string $offerId,
        public string $title,
        public string $description,
        public string $companyName,
        public string $contractType,
        public string $applyUrl,
        public \DateTimeImmutable $publishedAt,
    ) {
    }
}
