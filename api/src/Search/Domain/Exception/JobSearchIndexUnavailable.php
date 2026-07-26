<?php

declare(strict_types=1);

namespace Search\Domain\Exception;

use Shared\Domain\DomainException;

final class JobSearchIndexUnavailable extends \DomainException implements DomainException
{
    public static function dueTo(\Throwable $cause): self
    {
        return new self('The job search index could not be reached.', previous: $cause);
    }
}
