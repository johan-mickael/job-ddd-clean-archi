<?php

declare(strict_types=1);

namespace Identity\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidEmail extends \DomainException implements DomainException
{
    public static function malformed(string $value): self
    {
        return new self(\sprintf('"%s" is not a valid email address.', $value));
    }
}
