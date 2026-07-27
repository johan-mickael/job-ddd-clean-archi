<?php

declare(strict_types=1);

namespace Identity\Domain;

use Identity\Domain\Exception\InvalidEmail;

final readonly class Email
{
    /** @var non-empty-string */
    public string $value;

    public function __construct(string $value)
    {
        $normalized = \mb_strtolower(\trim($value));

        if ($normalized === '' || \filter_var($normalized, \FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidEmail::malformed($value);
        }

        $this->value = $normalized;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
