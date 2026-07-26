<?php

declare(strict_types=1);

namespace Tests\Catalog\Infrastructure\Persistence\Doctrine;

use Catalog\Domain\ApplyUrl;
use Catalog\Domain\ContractType;
use Catalog\Domain\ExperienceRequirement;
use Catalog\Domain\Job;
use Catalog\Domain\JobDetails;
use Catalog\Domain\JobId;
use Catalog\Domain\JobSkill;
use Catalog\Domain\WorkLocation;
use Catalog\Infrastructure\Persistence\Doctrine\JobMapper;
use PHPUnit\Framework\TestCase;
use Shared\Domain\RomeCode;
use Shared\Domain\RomeCompetencyCode;

final class JobMapperTest extends TestCase
{
    public function testANewlyPublishedJobRoundTripsThroughTheEntity(): void
    {
        $mapper = new JobMapper();
        $job = $this->aFullyDetailedJob();

        $reconstituted = $mapper->toDomain($mapper->toNewEntity($job));

        self::assertSame($job->id->value, $reconstituted->id->value);
        self::assertSame($job->title, $reconstituted->title);
        self::assertSame($job->contractType, $reconstituted->contractType);
        self::assertEquals($job->applyUrl, $reconstituted->applyUrl);
        self::assertSame($job->status(), $reconstituted->status());
        self::assertSame($job->details->location?->label, $reconstituted->details->location?->label);
        self::assertSame($job->details->romeCode?->value, $reconstituted->details->romeCode?->value);
        self::assertSame($job->details->experienceRequirement, $reconstituted->details->experienceRequirement);
        self::assertCount(1, $reconstituted->details->skills);
        self::assertSame('PHP', $reconstituted->details->skills[0]->label);
        self::assertSame('104232', $reconstituted->details->skills[0]->romeCompetencyCode?->value);
    }

    public function testAnExpiredJobRoundTripsAsExpired(): void
    {
        $mapper = new JobMapper();
        $job = $this->aFullyDetailedJob();
        $job->expire();

        $reconstituted = $mapper->toDomain($mapper->toNewEntity($job));

        self::assertTrue($reconstituted->isExpired());
    }

    public function testUpdateEntityOverwritesAnExistingEntityInPlace(): void
    {
        $mapper = new JobMapper();
        $entity = $mapper->toNewEntity($this->aFullyDetailedJob());

        $renamedJob = Job::publish(
            new JobId('168NPMR'),
            'Développeur PHP Lead',
            'Nouvelle description',
            'Acme Corp',
            ContractType::FixedTermContract,
            new ApplyUrl('https://candidat.francetravail.fr/offres/recherche/detail/168NPMR'),
            new \DateTimeImmutable('2026-07-02'),
        );
        $mapper->updateEntity($entity, $renamedJob);

        self::assertSame('168NPMR', $entity->id);
        self::assertSame('Développeur PHP Lead', $entity->title);
        self::assertSame('cdd', $entity->contractType);
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
                romeLabel: 'Études et développement informatique',
                experienceRequirement: ExperienceRequirement::Required,
                skills: [new JobSkill('PHP', new RomeCompetencyCode('104232'), true)],
            ),
        );
    }
}
