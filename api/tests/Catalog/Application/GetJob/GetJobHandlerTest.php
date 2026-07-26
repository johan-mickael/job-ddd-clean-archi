<?php

declare(strict_types=1);

namespace Tests\Catalog\Application\GetJob;

use Catalog\Application\ExpireJob\ExpireJob;
use Catalog\Application\ExpireJob\ExpireJobHandler;
use Catalog\Application\GetJob\GetJob;
use Catalog\Application\GetJob\GetJobHandler;
use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandler;
use Catalog\Application\PublishJob\PublishJobSkill;
use Catalog\Domain\Exception\JobNotFound;
use PHPUnit\Framework\TestCase;
use Tests\Catalog\InMemoryJobRepository;
use Tests\Catalog\RecordingEventPublisher;

final class GetJobHandlerTest extends TestCase
{
    public function testItReturnsAFullViewOfAPublishedJob(): void
    {
        $jobs = new InMemoryJobRepository();
        (new PublishJobHandler($jobs, new RecordingEventPublisher()))(new PublishJob(
            jobId: '168NPMR',
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            publishedAt: new \DateTimeImmutable('2026-07-01'),
            locationLabel: 'Lyon - 69003',
            romeCode: 'M1805',
            skills: [new PublishJobSkill('PHP', '104232', true)],
        ));

        $view = (new GetJobHandler($jobs))(new GetJob('168NPMR'));

        self::assertSame('168NPMR', $view->jobId);
        self::assertSame('published', $view->status);
        self::assertSame('Lyon - 69003', $view->locationLabel);
        self::assertSame('M1805', $view->romeCode);
        self::assertCount(1, $view->skills);
        self::assertSame('PHP', $view->skills[0]->label);
    }

    public function testItReflectsExpiryInTheStatus(): void
    {
        $jobs = new InMemoryJobRepository();
        $events = new RecordingEventPublisher();
        (new PublishJobHandler($jobs, $events))(new PublishJob(
            jobId: '168NPMR',
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            publishedAt: new \DateTimeImmutable('2026-07-01'),
        ));
        (new ExpireJobHandler($jobs, $events))(new ExpireJob('168NPMR'));

        $view = (new GetJobHandler($jobs))(new GetJob('168NPMR'));

        self::assertSame('expired', $view->status);
    }

    public function testItThrowsWhenTheJobDoesNotExist(): void
    {
        $this->expectException(JobNotFound::class);

        (new GetJobHandler(new InMemoryJobRepository()))(new GetJob('unknown'));
    }
}
