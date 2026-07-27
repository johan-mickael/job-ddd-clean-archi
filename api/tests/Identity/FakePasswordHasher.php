<?php

declare(strict_types=1);

namespace Tests\Identity;

use Identity\Domain\PasswordHasherInterface;

final class FakePasswordHasher implements PasswordHasherInterface
{
    private const string PREFIX = 'hashed:';

    public function hash(string $plainPassword): string
    {
        return self::PREFIX . $plainPassword;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $hashedPassword === self::PREFIX . $plainPassword;
    }
}
