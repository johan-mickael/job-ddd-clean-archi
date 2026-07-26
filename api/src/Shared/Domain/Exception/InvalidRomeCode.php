<?php

declare(strict_types=1);

namespace Shared\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidRomeCode extends \DomainException implements DomainException
{
    public static function malformed(string $value): self
    {
        return new self(\sprintf('"%s" is not a valid ROME code (expected one letter followed by 4 digits).', $value));
    }
}
