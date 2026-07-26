<?php

declare(strict_types=1);

namespace Catalog\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidWorkLocation extends \DomainException implements DomainException
{
    public static function blankLabel(): self
    {
        return new self('A work location cannot have a blank label.');
    }
}
