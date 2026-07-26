<?php

declare(strict_types=1);

namespace Identity\Domain;

use Identity\Domain\Exception\InvalidUserId;

final readonly class UserId
{
    public function __construct(public string $value)
    {
        if (\trim($value) === '') {
            throw InvalidUserId::blank();
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
