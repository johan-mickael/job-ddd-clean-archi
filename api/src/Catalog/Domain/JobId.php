<?php

declare(strict_types=1);

namespace Catalog\Domain;

use Catalog\Domain\Exception\InvalidJobId;

final readonly class JobId
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw InvalidJobId::blank();
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
