<?php

declare(strict_types=1);

namespace Catalog\Infrastructure\Persistence\Doctrine;

use Catalog\Domain\Exception\JobNotFound;
use Catalog\Domain\Job;
use Catalog\Domain\JobId;
use Catalog\Domain\JobRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineJobRepository implements JobRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JobMapper $mapper,
    ) {}

    public function ofId(JobId $id): Job
    {
        $entity = $this->entityManager->find(JobEntity::class, $id->value);

        return $entity !== null ? $this->mapper->toDomain($entity) : throw JobNotFound::withId($id);
    }

    public function add(Job $job): void
    {
        $entity = $this->entityManager->find(JobEntity::class, $job->id->value);

        if ($entity === null) {
            $this->entityManager->persist($this->mapper->toNewEntity($job));
        } else {
            $this->mapper->updateEntity($entity, $job);
        }

        $this->entityManager->flush();
    }

    public function publishedJobIds(): array
    {
        $ids = $this->entityManager->createQueryBuilder()
            ->select('entity.id')
            ->from(JobEntity::class, 'entity')
            ->where('entity.status = :status')
            ->setParameter('status', 'published')
            ->getQuery()
            ->getSingleColumnResult();

        return \array_values(\array_map(static function (mixed $id): JobId {
            \assert(\is_string($id));

            return new JobId($id);
        }, $ids));
    }
}
