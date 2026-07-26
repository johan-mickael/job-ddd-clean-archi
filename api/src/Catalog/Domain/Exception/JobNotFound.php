<?php

declare(strict_types=1);

namespace Catalog\Domain\Exception;

use Catalog\Domain\JobId;
use Shared\Domain\DomainException;

final class JobNotFound extends \DomainException implements DomainException
{
    public static function withId(JobId $jobId): self
    {
        return new self(\sprintf('No job found for id "%s".', $jobId->value));
    }
}
