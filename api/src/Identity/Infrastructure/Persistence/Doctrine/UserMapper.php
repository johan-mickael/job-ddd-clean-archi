<?php

declare(strict_types=1);

namespace Identity\Infrastructure\Persistence\Doctrine;

use Identity\Domain\Email;
use Identity\Domain\User;
use Identity\Domain\UserId;

final class UserMapper
{
    public function toNewEntity(User $user): UserEntity
    {
        $entity = new UserEntity();
        $entity->id = $user->id->value;
        $entity->email = $user->email->value;
        $entity->hashedPassword = $user->hashedPassword;

        return $entity;
    }

    public function toDomain(UserEntity $entity): User
    {
        return User::reconstitute(
            new UserId($entity->id),
            new Email($entity->email),
            $entity->hashedPassword,
        );
    }
}
