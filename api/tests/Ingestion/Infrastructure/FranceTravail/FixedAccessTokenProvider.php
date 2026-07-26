<?php

declare(strict_types=1);

namespace Tests\Ingestion\Infrastructure\FranceTravail;

use Ingestion\Infrastructure\FranceTravail\AccessToken;
use Ingestion\Infrastructure\FranceTravail\AccessTokenProvider;

final class FixedAccessTokenProvider implements AccessTokenProvider
{
    public function provideToken(): AccessToken
    {
        return new AccessToken('fixed-token', new \DateTimeImmutable('+1 hour'));
    }
}
