<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\FranceTravail;

use Ingestion\Infrastructure\FranceTravail\Exception\AccessTokenRequestFailed;

interface AccessTokenProvider
{
    /**
     * @throws AccessTokenRequestFailed
     */
    public function provideToken(): AccessToken;
}
