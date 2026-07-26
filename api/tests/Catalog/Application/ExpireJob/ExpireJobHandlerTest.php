<?php

declare(strict_types=1);

namespace Tests\Catalog\Application\ExpireJob;

use Catalog\Application\ExpireJob\ExpireJob;
use Catalog\Application\ExpireJob\ExpireJobHandler;
use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandler;
use Catalog\Domain\Exception\OfferAlreadyExpired;
use Catalog\Domain\JobId;
use PHPUnit\Framework\TestCase;
use Tests\Catalog\InMemoryJobRepository;
use Tests\Catalog\RecordingEventPublisher;

final class ExpireJobHandlerTest extends TestCase
{
    public function testItExpiresAPublishedJob(): void
    {
        $jobs = new InMemoryJobRepository();
        (new PublishJobHandler($jobs, new RecordingEventPublisher()))($this->publishCommand());

        (new ExpireJobHandler($jobs, new RecordingEventPublisher()))(new ExpireJob('168NPMR'));

        self::assertTrue($jobs->ofId(new JobId('168NPMR'))->isExpired());
    }

    public function testExpiringAnAlreadyExpiredJobThrows(): void
    {
        $jobs = new InMemoryJobRepository();
        (new PublishJobHandler($jobs, new RecordingEventPublisher()))($this->publishCommand());
        $handler = new ExpireJobHandler($jobs, new RecordingEventPublisher());
        $handler(new ExpireJob('168NPMR'));

        $this->expectException(OfferAlreadyExpired::class);

        $handler(new ExpireJob('168NPMR'));
    }

    private function publishCommand(): PublishJob
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
