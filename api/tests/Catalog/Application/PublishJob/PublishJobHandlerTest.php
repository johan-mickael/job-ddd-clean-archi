<?php

declare(strict_types=1);

namespace Tests\Catalog\Application\PublishJob;

use Catalog\Domain\Exception\InvalidContractType;
use Catalog\Domain\ExperienceRequirement;
use Catalog\Domain\JobId;
use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandler;
use Catalog\Application\PublishJob\PublishJobSkill;
use PHPUnit\Framework\TestCase;

final class PublishJobHandlerTest extends TestCase
{
    public function testItPublishesAndPersistsTheJob(): void
    {
        $jobs = new InMemoryJobRepository();
        $handler = new PublishJobHandler($jobs);

        ($handler)($this->command());

        self::assertSame('published', $jobs->ofId(new JobId('168NPMR'))->status()->value);
    }

    public function testItBuildsTheDetailsBundleFromAFullyPopulatedCommand(): void
    {
        $jobs = new InMemoryJobRepository();
        $handler = new PublishJobHandler($jobs);

        ($handler)(new PublishJob(
            jobId: '168NPMR',
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            publishedAt: new \DateTimeImmutable('2026-07-01'),
            companyLogoUrl: 'https://cdn.francetravail.fr/logos/168NPMR.png',
            companyDescription: 'Éditeur de logiciels RH.',
            locationLabel: 'Lyon - 69003',
            locationInseeCode: '69383',
            locationPostalCode: '69003',
            romeCode: 'M1805',
            romeLabel: 'Études et développement informatique',
            experienceRequirement: 'required',
            isWorkStudy: true,
            salaryDescription: '40 - 50 k€ brut annuel',
            numberOfPositions: 2,
            skills: [
                new PublishJobSkill('PHP', '104232', true),
                new PublishJobSkill('Symfony', null, false),
            ],
            updatedAt: new \DateTimeImmutable('2026-07-10'),
        ));

        $details = $jobs->ofId(new JobId('168NPMR'))->details;

        self::assertSame('Lyon - 69003', $details->location?->label);
        self::assertSame('69383', $details->location?->inseeCode);
        self::assertSame('M1805', $details->romeCode?->value);
        self::assertSame(ExperienceRequirement::Required, $details->experienceRequirement);
        self::assertTrue($details->isWorkStudy);
        self::assertSame(2, $details->numberOfPositions);
        self::assertCount(2, $details->skills);
        self::assertSame('104232', $details->skills[0]->romeCompetencyCode?->value);
        self::assertNull($details->skills[1]->romeCompetencyCode);
    }

    public function testUnrecognizedExperienceRequirementIsDroppedRatherThanFailingThePublish(): void
    {
        $jobs = new InMemoryJobRepository();
        $handler = new PublishJobHandler($jobs);

        ($handler)(new PublishJob(
            jobId: '168NPMR',
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            publishedAt: new \DateTimeImmutable('2026-07-01'),
            experienceRequirement: 'not-a-real-code',
        ));

        self::assertNull($jobs->ofId(new JobId('168NPMR'))->details->experienceRequirement);
    }

    public function testDefaultNumberOfPositionsIsOneWhenNotSpecified(): void
    {
        $jobs = new InMemoryJobRepository();
        $handler = new PublishJobHandler($jobs);

        ($handler)($this->command());

        self::assertSame(1, $jobs->ofId(new JobId('168NPMR'))->details->numberOfPositions);
    }

    public function testItRejectsAnUnrecognizedContractType(): void
    {
        $handler = new PublishJobHandler(new InMemoryJobRepository());

        $this->expectException(InvalidContractType::class);

        ($handler)(new PublishJob(
            jobId: '168NPMR',
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'not-a-real-code',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            publishedAt: new \DateTimeImmutable('2026-07-01'),
        ));
    }

    private function command(): PublishJob
    {
        return new PublishJob(
            jobId: '168NPMR',
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            publishedAt: new \DateTimeImmutable('2026-07-01'),
        );
    }
}
