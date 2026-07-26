<?php

declare(strict_types=1);

namespace Catalog\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidJobId extends \DomainException implements DomainException
{
    public static function blank(): self
    {
        return new self('A job id cannot be blank.');
    }
}
