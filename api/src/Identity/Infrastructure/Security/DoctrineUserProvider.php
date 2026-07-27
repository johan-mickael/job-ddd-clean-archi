<?php

declare(strict_types=1);

namespace Identity\Infrastructure\Security;

use Identity\Domain\Email;
use Identity\Domain\Exception\UserNotFound;
use Identity\Domain\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<SecurityUser>
 */
final class DoctrineUserProvider implements UserProviderInterface
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            return new SecurityUser($this->users->ofEmail(new Email($identifier)));
        } catch (UserNotFound $e) {
            throw new UserNotFoundException(previous: $e);
        }
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(\sprintf('Unsupported user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class;
    }
}
