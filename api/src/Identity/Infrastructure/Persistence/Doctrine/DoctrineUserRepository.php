<?php

declare(strict_types=1);

namespace Identity\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Identity\Domain\Email;
use Identity\Domain\Exception\UserNotFound;
use Identity\Domain\User;
use Identity\Domain\UserId;
use Identity\Domain\UserRepositoryInterface;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserMapper $mapper,
    ) {}

    public function ofId(UserId $id): User
    {
        $entity = $this->entityManager->find(UserEntity::class, $id->value);

        return $entity !== null ? $this->mapper->toDomain($entity) : throw UserNotFound::withId($id);
    }

    public function ofEmail(Email $email): User
    {
        $entity = $this->findByEmail($email);

        return $entity !== null ? $this->mapper->toDomain($entity) : throw UserNotFound::withEmail($email);
    }

    public function existsWithEmail(Email $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public function add(User $user): void
    {
        $this->entityManager->persist($this->mapper->toNewEntity($user));
        $this->entityManager->flush();
    }

    private function findByEmail(Email $email): ?UserEntity
    {
        return $this->entityManager->getRepository(UserEntity::class)->findOneBy(['email' => $email->value]);
    }
}
