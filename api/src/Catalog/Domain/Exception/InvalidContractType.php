<?php

declare(strict_types=1);

namespace Catalog\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidContractType extends \DomainException implements DomainException
{
    public static function unrecognized(string $code): self
    {
        return new self(\sprintf('"%s" is not a recognized contract type.', $code));
    }
}
