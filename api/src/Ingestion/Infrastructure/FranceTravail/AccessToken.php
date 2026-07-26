<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\FranceTravail;

final readonly class AccessToken
{
    public function __construct(
        public string $value,
        public \DateTimeImmutable $expiresAt,
    ) {
    }

    public function hasExpired(\DateTimeImmutable $now): bool
    {
        return $now >= $this->expiresAt;
    }
}
