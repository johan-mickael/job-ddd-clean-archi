<?php

declare(strict_types=1);

namespace CandidateProfile\Domain\Exception;

use Shared\Domain\DomainException;

final class CvProfileExtractionFailed extends \DomainException implements DomainException
{
    public static function dueTo(\Throwable $cause): self
    {
        return new self('The CV structured-extraction provider could not be reached.', previous: $cause);
    }
}
