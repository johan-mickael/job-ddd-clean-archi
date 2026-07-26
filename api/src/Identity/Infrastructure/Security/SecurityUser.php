<?php

declare(strict_types=1);

namespace Identity\Infrastructure\Security;

use Identity\Domain\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Symfony Security's UserInterface is inherently framework-specific - this
 * adapts Identity\Domain\User to it instead of making the domain aggregate
 * implement a Symfony interface directly.
 */
final readonly class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public string $userId;

    /** @var non-empty-string */
    public string $email;

    public string $hashedPassword;

    public function __construct(User $user)
    {
        $this->userId = $user->id->value;
        $this->email = $user->email->value;
        $this->hashedPassword = $user->hashedPassword;
    }

    public function getPassword(): string
    {
        return $this->hashedPassword;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void {}
}
