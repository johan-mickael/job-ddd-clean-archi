<?php

declare(strict_types=1);

namespace Tests\Ingestion\Application\SyncJobOffers;

use Catalog\Application\PublishJob\PublishJobHandler;
use Catalog\Domain\JobId;
use Ingestion\Application\SyncJobOffers\SyncJobOffers;
use Ingestion\Application\SyncJobOffers\SyncJobOffersHandler;
use Ingestion\Domain\IncomingJobOffer;
use Ingestion\Domain\IncomingJobSkill;
use PHPUnit\Framework\TestCase;
use Tests\Catalog\InMemoryJobRepository;

final class SyncJobOffersHandlerTest extends TestCase
{
    public function testItPublishesEveryOfferFromTheSource(): void
    {
        $jobs = new InMemoryJobRepository();
        $handler = new SyncJobOffersHandler(
            new FakeJobOfferSource([$this->incomingOffer('168NPMR'), $this->incomingOffer('168NPMS')]),
            new PublishJobHandler($jobs),
        );

        $seenOfferIds = ($handler)(new SyncJobOffers(new \DateTimeImmutable('-1 day')));

        self::assertSame('published', $jobs->ofId(new JobId('168NPMR'))->status()->value);
        self::assertSame('published', $jobs->ofId(new JobId('168NPMS'))->status()->value);
        self::assertSame(['168NPMR', '168NPMS'], $seenOfferIds);
    }

    public function testItCarriesEnrichmentFieldsAndSkillsThroughToTheJob(): void
    {
        $jobs = new InMemoryJobRepository();
        $handler = new SyncJobOffersHandler(
            new FakeJobOfferSource([$this->incomingOffer('168NPMR')]),
            new PublishJobHandler($jobs),
        );

        ($handler)(new SyncJobOffers(new \DateTimeImmutable('-1 day')));

        $details = $jobs->ofId(new JobId('168NPMR'))->details;
        self::assertSame('Lyon - 69003', $details->location?->label);
        self::assertSame('M1805', $details->romeCode?->value);
        self::assertCount(1, $details->skills);
        self::assertSame('PHP', $details->skills[0]->label);
    }

    private function incomingOffer(string $offerId): IncomingJobOffer
    {
        return new IncomingJobOffer(
            offerId: $offerId,
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/' . $offerId,
            publishedAt: new \DateTimeImmutable('2026-07-01'),
            locationLabel: 'Lyon - 69003',
            locationInseeCode: '69383',
            romeCode: 'M1805',
            romeLabel: 'Études et développement informatique',
            skills: [new IncomingJobSkill('PHP', '104232', true)],
        );
    }
}
