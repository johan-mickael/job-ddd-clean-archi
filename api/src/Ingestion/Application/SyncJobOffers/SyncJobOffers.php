<?php

declare(strict_types=1);

namespace Ingestion\Application\SyncJobOffers;

final readonly class SyncJobOffers
{
    public function __construct(public \DateTimeImmutable $since)
    {
    }
}
