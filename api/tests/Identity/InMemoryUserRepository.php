<?php

declare(strict_types=1);

namespace Tests\Identity;

use Identity\Domain\Email;
use Identity\Domain\Exception\UserNotFound;
use Identity\Domain\User;
use Identity\Domain\UserId;
use Identity\Domain\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<string, User> */
    private array $users = [];

    public function ofId(UserId $id): User
    {
        return $this->users[$id->value] ?? throw UserNotFound::withId($id);
    }

    public function ofEmail(Email $email): User
    {
        foreach ($this->users as $user) {
            if ($user->email->equals($email)) {
                return $user;
            }
        }

        throw UserNotFound::withEmail($email);
    }

    public function existsWithEmail(Email $email): bool
    {
        foreach ($this->users as $user) {
            if ($user->email->equals($email)) {
                return true;
            }
        }

        return false;
    }

    public function add(User $user): void
    {
        $this->users[$user->id->value] = $user;
    }
}
