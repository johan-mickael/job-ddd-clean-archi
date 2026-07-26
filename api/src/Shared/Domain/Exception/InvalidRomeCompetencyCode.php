<?php

declare(strict_types=1);

namespace Shared\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidRomeCompetencyCode extends \DomainException implements DomainException
{
    public static function blank(): self
    {
        return new self('A ROME competency code cannot be blank.');
    }
}
