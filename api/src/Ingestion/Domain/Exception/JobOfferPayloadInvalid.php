<?php

declare(strict_types=1);

namespace Ingestion\Domain\Exception;

use Shared\Domain\DomainException;

final class JobOfferPayloadInvalid extends \DomainException implements DomainException
{
    public static function missingField(string $field): self
    {
        return new self(\sprintf('France Travail offer payload is missing required field "%s".', $field));
    }
}
