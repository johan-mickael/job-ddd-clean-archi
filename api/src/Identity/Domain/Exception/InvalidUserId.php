<?php

declare(strict_types=1);

namespace Identity\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidUserId extends \DomainException implements DomainException
{
    public static function blank(): self
    {
        return new self('User id cannot be blank.');
    }
}
