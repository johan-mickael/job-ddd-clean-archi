<?php

declare(strict_types=1);

namespace CandidateProfile\Domain\Exception;

use CandidateProfile\Domain\CandidateProfileId;
use Shared\Domain\DomainException;

final class CandidateProfileNotFound extends \DomainException implements DomainException
{
    public static function withId(CandidateProfileId $id): self
    {
        return new self(\sprintf('No candidate profile found for id "%s".', $id->value));
    }
}
