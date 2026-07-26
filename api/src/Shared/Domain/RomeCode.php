<?php

declare(strict_types=1);

namespace Shared\Domain;

use Shared\Domain\Exception\InvalidRomeCode;

final readonly class RomeCode
{
    public function __construct(public string $value)
    {
        if (\preg_match('/^[A-Z]\d{4}$/', $value) !== 1) {
            throw InvalidRomeCode::malformed($value);
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
