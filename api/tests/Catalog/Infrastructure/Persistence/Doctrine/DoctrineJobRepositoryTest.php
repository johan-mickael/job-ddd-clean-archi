<?php

declare(strict_types=1);

namespace Tests\Catalog\Infrastructure\Persistence\Doctrine;

use Catalog\Domain\ApplyUrl;
use Catalog\Domain\ContractType;
use Catalog\Domain\Exception\JobNotFound;
use Catalog\Domain\Job;
use Catalog\Domain\JobDetails;
use Catalog\Domain\JobId;
use Catalog\Domain\JobSkill;
use Catalog\Domain\WorkLocation;
use Catalog\Infrastructure\Persistence\Doctrine\DoctrineJobRepository;
use Catalog\Infrastructure\Persistence\Doctrine\JobEntity;
use Catalog\Infrastructure\Persistence\Doctrine\JobMapper;
use Doctrine\ORM\EntityManagerInterface;
use Shared\Domain\RomeCode;
use Shared\Domain\RomeCompetencyCode;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineJobRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DoctrineJobRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;
        $this->repository = new DoctrineJobRepository($this->entityManager, new JobMapper());
        $this->entityManager->createQuery('DELETE FROM ' . JobEntity::class)->execute();
    }

    public function testANewlyAddedJobCanBeReadBackByIdWithFullDetails(): void
    {
        $this->repository->add($this->aFullyDetailedJob());
        $this->entityManager->clear();

        $job = $this->repository->ofId(new JobId('168NPMR'));

        self::assertSame('Développeur PHP Senior', $job->title);
        self::assertSame(ContractType::PermanentContract, $job->contractType);
        self::assertSame('published', $job->status()->value);
        self::assertSame('Lyon - 69003', $job->details->location?->label);
        self::assertSame('M1805', $job->details->romeCode?->value);
        self::assertCount(1, $job->details->skills);
        self::assertSame('104232', $job->details->skills[0]->romeCompetencyCode?->value);
    }

    public function testOfIdThrowsWhenTheJobDoesNotExist(): void
    {
        $this->expectException(JobNotFound::class);

        $this->repository->ofId(new JobId('UNKNOWN'));
    }

    public function testAddingAJobWithAnExistingIdUpdatesItInPlaceInsteadOfDuplicating(): void
    {
        $this->repository->add($this->aFullyDetailedJob());

        $renamedJob = Job::publish(
            new JobId('168NPMR'),
            'Développeur PHP Lead',
            'Nouvelle description',
            'Acme Corp',
            ContractType::PermanentContract,
            new ApplyUrl('https://candidat.francetravail.fr/offres/recherche/detail/168NPMR'),
            new \DateTimeImmutable('2026-07-02'),
        );
        $this->repository->add($renamedJob);
        $this->entityManager->clear();

        self::assertSame('Développeur PHP Lead', $this->repository->ofId(new JobId('168NPMR'))->title);

        $count = $this->entityManager->createQuery('SELECT COUNT(entity.id) FROM ' . JobEntity::class . ' entity')->getSingleScalarResult();
        self::assertSame(1, (int) $count);
    }

    public function testPublishedJobIdsExcludesExpiredJobs(): void
    {
        $activeJob = $this->aFullyDetailedJob();
        $this->repository->add($activeJob);

        $expiredJob = Job::publish(
            new JobId('168EXPR'),
            'Offre expirée',
            'Description',
            'Acme Corp',
            ContractType::PermanentContract,
            new ApplyUrl('https://candidat.francetravail.fr/offres/recherche/detail/168EXPR'),
            new \DateTimeImmutable('2026-07-01'),
        );
        $expiredJob->expire();
        $this->repository->add($expiredJob);

        $ids = \array_map(static fn(JobId $id): string => $id->value, $this->repository->publishedJobIds());

        self::assertContains('168NPMR', $ids);
        self::assertNotContains('168EXPR', $ids);
    }

    private function aFullyDetailedJob(): Job
    {
        return Job::publish(
            new JobId('168NPMR'),
            'Développeur PHP Senior',
            'Vous rejoignez une équipe backend...',
            'Acme Corp',
            ContractType::PermanentContract,
            new ApplyUrl('https://candidat.francetravail.fr/offres/recherche/detail/168NPMR'),
            new \DateTimeImmutable('2026-07-01'),
            new JobDetails(
                location: new WorkLocation('Lyon - 69003', '69383', '69003'),
                romeCode: new RomeCode('M1805'),
                skills: [new JobSkill('PHP', new RomeCompetencyCode('104232'), true)],
            ),
        );
    }
}
