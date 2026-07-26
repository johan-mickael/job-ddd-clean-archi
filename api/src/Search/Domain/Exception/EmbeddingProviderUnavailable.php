<?php

declare(strict_types=1);

namespace Search\Domain\Exception;

use Shared\Domain\DomainException;

final class EmbeddingProviderUnavailable extends \DomainException implements DomainException
{
    public static function dueTo(\Throwable $cause): self
    {
        return new self('The embedding provider could not be reached.', previous: $cause);
    }
}
