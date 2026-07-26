<?php

declare(strict_types=1);

namespace Ingestion\Domain\Exception;

use Shared\Domain\DomainException;

final class JobOfferSourceUnavailable extends \DomainException implements DomainException
{
    public static function dueTo(\Throwable $cause): self
    {
        return new self('The job offer source could not be reached.', previous: $cause);
    }
}
