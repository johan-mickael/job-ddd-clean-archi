<?php

declare(strict_types=1);

namespace CandidateProfile\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidCandidateProfileId extends \DomainException implements DomainException
{
    public static function blank(): self
    {
        return new self('Candidate profile id cannot be blank.');
    }
}
