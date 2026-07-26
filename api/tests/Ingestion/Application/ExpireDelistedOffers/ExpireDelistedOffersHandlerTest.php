<?php

declare(strict_types=1);

namespace Tests\Ingestion\Application\ExpireDelistedOffers;

use Catalog\Application\ExpireJob\ExpireJobHandler;
use Catalog\Application\ListPublishedJobIds\ListPublishedJobIdsHandler;
use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandler;
use Catalog\Domain\JobId;
use Ingestion\Application\ExpireDelistedOffers\ExpireDelistedOffers;
use Ingestion\Application\ExpireDelistedOffers\ExpireDelistedOffersHandler;
use PHPUnit\Framework\TestCase;
use Tests\Catalog\InMemoryJobRepository;
use Tests\Catalog\RecordingEventPublisher;

final class ExpireDelistedOffersHandlerTest extends TestCase
{
    public function testOffersMissingFromTheSeenSetAreExpired(): void
    {
        $jobs = new InMemoryJobRepository();
        $publishJob = new PublishJobHandler($jobs, new RecordingEventPublisher());
        $publishJob($this->publishCommand('168NPMR'));
        $publishJob($this->publishCommand('168NPMS'));
        $publishJob($this->publishCommand('168NPMT'));

        $handler = new ExpireDelistedOffersHandler(new ListPublishedJobIdsHandler($jobs), new ExpireJobHandler($jobs, new RecordingEventPublisher()));
        $handler(new ExpireDelistedOffers(['168NPMR', '168NPMT']));

        self::assertFalse($jobs->ofId(new JobId('168NPMR'))->isExpired());
        self::assertTrue($jobs->ofId(new JobId('168NPMS'))->isExpired());
        self::assertFalse($jobs->ofId(new JobId('168NPMT'))->isExpired());
    }

    public function testNothingIsExpiredWhenEveryPublishedJobWasSeen(): void
    {
        $jobs = new InMemoryJobRepository();
        $publishJob = new PublishJobHandler($jobs, new RecordingEventPublisher());
        $publishJob($this->publishCommand('168NPMR'));

        $handler = new ExpireDelistedOffersHandler(new ListPublishedJobIdsHandler($jobs), new ExpireJobHandler($jobs, new RecordingEventPublisher()));
        $handler(new ExpireDelistedOffers(['168NPMR']));

        self::assertFalse($jobs->ofId(new JobId('168NPMR'))->isExpired());
    }

    private function publishCommand(string $jobId): PublishJob
    {
        return new PublishJob(
            jobId: $jobId,
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/' . $jobId,
            publishedAt: new \DateTimeImmutable('2026-07-01'),
        );
    }
}
