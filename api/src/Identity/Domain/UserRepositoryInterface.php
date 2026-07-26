<?php

declare(strict_types=1);

namespace Identity\Domain;

use Identity\Domain\Exception\UserNotFound;

interface UserRepositoryInterface
{
    /**
     * @throws UserNotFound
     */
    public function ofId(UserId $id): User;

    /**
     * @throws UserNotFound
     */
    public function ofEmail(Email $email): User;

    public function existsWithEmail(Email $email): bool;

    public function add(User $user): void;
}
