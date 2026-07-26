<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\FranceTravail\Exception;

final class AccessTokenRequestFailed extends \RuntimeException
{
    public static function authenticationFailed(\Throwable $cause): self
    {
        return new self('Could not authenticate with France Travail.', previous: $cause);
    }

    public static function malformedResponse(): self
    {
        return new self('France Travail token response is missing access_token or expires_in.');
    }
}
