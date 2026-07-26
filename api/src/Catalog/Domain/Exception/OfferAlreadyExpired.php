<?php

declare(strict_types=1);

namespace Catalog\Domain\Exception;

use Catalog\Domain\JobId;
use Shared\Domain\DomainException;

final class OfferAlreadyExpired extends \DomainException implements DomainException
{
    public static function forJob(JobId $jobId): self
    {
        return new self(\sprintf('Job "%s" is already expired.', $jobId->value));
    }
}
