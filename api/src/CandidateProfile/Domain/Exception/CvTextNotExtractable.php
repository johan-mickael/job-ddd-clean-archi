<?php

declare(strict_types=1);

namespace CandidateProfile\Domain\Exception;

use Shared\Domain\DomainException;

final class CvTextNotExtractable extends \DomainException implements DomainException
{
    public static function unsupportedFileType(string $fileName): self
    {
        return new self(\sprintf('"%s" is not a PDF or Word document.', $fileName));
    }

    public static function dueTo(string $fileName, \Throwable $previous): self
    {
        return new self(\sprintf('Could not extract text from "%s".', $fileName), previous: $previous);
    }
}
