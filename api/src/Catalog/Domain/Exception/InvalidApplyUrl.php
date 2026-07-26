<?php

declare(strict_types=1);

namespace Catalog\Domain\Exception;

use Shared\Domain\DomainException;

final class InvalidApplyUrl extends \DomainException implements DomainException
{
    public static function notAnHttpUrl(string $value): self
    {
        return new self(\sprintf('"%s" is not a valid http(s) apply URL.', $value));
    }
}
