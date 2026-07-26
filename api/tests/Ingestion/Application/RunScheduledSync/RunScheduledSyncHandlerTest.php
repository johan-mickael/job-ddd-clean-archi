<?php

declare(strict_types=1);

namespace Tests\Ingestion\Application\RunScheduledSync;

use Catalog\Application\ExpireJob\ExpireJobHandler;
use Catalog\Application\ListPublishedJobIds\ListPublishedJobIdsHandler;
use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandler;
use Catalog\Domain\JobId;
use Ingestion\Application\ExpireDelistedOffers\ExpireDelistedOffersHandler;
use Ingestion\Application\RunScheduledSync\RunScheduledSyncHandler;
use Ingestion\Application\SyncJobOffers\SyncJobOffersHandler;
use Ingestion\Domain\IncomingJobOffer;
use PHPUnit\Framework\TestCase;
use Tests\Catalog\InMemoryJobRepository;
use Tests\Ingestion\Application\SyncJobOffers\FakeJobOfferSource;

final class RunScheduledSyncHandlerTest extends TestCase
{
    public function testOffersNoLongerReturnedBySourceAreExpiredAfterSync(): void
    {
        $jobs = new InMemoryJobRepository();
        $publishJob = new PublishJobHandler($jobs);
        $publishJob($this->publishCommand('168OLD1'));

        $handler = new RunScheduledSyncHandler(
            new SyncJobOffersHandler(new FakeJobOfferSource([$this->incomingOffer('168NPMR')]), $publishJob),
            new ExpireDelistedOffersHandler(new ListPublishedJobIdsHandler($jobs), new ExpireJobHandler($jobs)),
        );

        ($handler)();

        self::assertTrue($jobs->ofId(new JobId('168OLD1'))->isExpired());
        self::assertFalse($jobs->ofId(new JobId('168NPMR'))->isExpired());
    }

    private function incomingOffer(string $offerId): IncomingJobOffer
    {
        return new IncomingJobOffer(
            offerId: $offerId,
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/'.$offerId,
            publishedAt: new \DateTimeImmutable('2026-07-01'),
        );
    }

    private function publishCommand(string $jobId): PublishJob
    {
        return new PublishJob(
            jobId: $jobId,
            title: 'Offre plus vue par la source',
            description: 'Description',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/'.$jobId,
            publishedAt: new \DateTimeImmutable('2026-01-01'),
        );
    }
}
