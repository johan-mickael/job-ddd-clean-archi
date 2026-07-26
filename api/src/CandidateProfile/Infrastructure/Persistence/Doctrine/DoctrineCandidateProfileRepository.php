<?php

declare(strict_types=1);

namespace CandidateProfile\Infrastructure\Persistence\Doctrine;

use CandidateProfile\Domain\CandidateProfile;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateProfileRepositoryInterface;
use CandidateProfile\Domain\Exception\CandidateProfileNotFound;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCandidateProfileRepository implements CandidateProfileRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CandidateProfileMapper $mapper,
    ) {}

    public function ofId(CandidateProfileId $id): CandidateProfile
    {
        $entity = $this->entityManager->find(CandidateProfileEntity::class, $id->value);

        return $entity !== null ? $this->mapper->toDomain($entity) : throw CandidateProfileNotFound::withId($id);
    }

    public function add(CandidateProfile $profile): void
    {
        $this->entityManager->persist($this->mapper->toNewEntity($profile));
        $this->entityManager->flush();
    }
}
