<?php

declare(strict_types=1);

namespace Identity\Domain\Exception;

use Shared\Domain\DomainException;

final class PasswordTooWeak extends \DomainException implements DomainException
{
    public static function tooShort(int $minimumLength): self
    {
        return new self(\sprintf('Password must be at least %d characters.', $minimumLength));
    }
}
