<?php

declare(strict_types=1);

namespace Identity\Domain\Exception;

use Identity\Domain\Email;
use Identity\Domain\UserId;
use Shared\Domain\DomainException;

final class UserNotFound extends \DomainException implements DomainException
{
    public static function withId(UserId $id): self
    {
        return new self(\sprintf('No user found for id "%s".', $id->value));
    }

    public static function withEmail(Email $email): self
    {
        return new self(\sprintf('No user found for email "%s".', $email->value));
    }
}
