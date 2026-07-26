<?php

declare(strict_types=1);

namespace CandidateProfile\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidCvProfileData extends \DomainException implements DomainException
{
    public static function becauseOfField(string $field, string $reason): self
    {
        return new self(\sprintf('Invalid CV profile data for field "%s": %s', $field, $reason));
    }
}
