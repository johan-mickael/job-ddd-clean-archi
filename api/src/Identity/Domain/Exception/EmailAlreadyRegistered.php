<?php

declare(strict_types=1);

namespace Identity\Domain\Exception;

use Identity\Domain\Email;
use Shared\Domain\DomainException;

final class EmailAlreadyRegistered extends \DomainException implements DomainException
{
    public static function withEmail(Email $email): self
    {
        return new self(\sprintf('"%s" is already registered.', $email->value));
    }
}
