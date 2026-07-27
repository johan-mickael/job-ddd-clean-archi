<?php

declare(strict_types=1);

namespace Identity\Infrastructure\Security;

use Identity\Domain\Email;
use Identity\Domain\PasswordHasherInterface;
use Identity\Domain\User;
use Identity\Domain\UserId;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Delegates to Symfony's own user-aware hasher (security.yaml's
 * password_hashers config for SecurityUser) rather than a standalone
 * hasher, so the algorithm used at registration is guaranteed to be the
 * same one json_login verifies against at login - no risk of the two
 * drifting apart.
 */
final class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher) {}

    public function hash(string $plainPassword): string
    {
        return $this->hasher->hashPassword($this->placeholderUser(), $plainPassword);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $this->hasher->isPasswordValid($this->placeholderUser($hashedPassword), $plainPassword);
    }

    private function placeholderUser(string $hashedPassword = ''): SecurityUser
    {
        return new SecurityUser(User::register(
            new UserId('00000000-0000-0000-0000-000000000000'),
            new Email('placeholder@example.com'),
            $hashedPassword,
        ));
    }
}
