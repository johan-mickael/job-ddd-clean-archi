<?php

declare(strict_types=1);

namespace Ingestion\Application\SyncJobOffers;

use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandlerInterface;
use Catalog\Application\PublishJob\PublishJobSkill;
use Ingestion\Domain\IncomingJobOffer;
use Ingestion\Domain\IncomingJobSkill;
use Ingestion\Domain\JobOfferSource;

final class SyncJobOffersHandler
{
    public function __construct(
        private readonly JobOfferSource $source,
        private readonly PublishJobHandlerInterface $publishJob,
    ) {
    }

    /**
     * @return list<string> ids of every offer processed, for delisting reconciliation
     */
    public function __invoke(SyncJobOffers $command): array
    {
        $seenOfferIds = [];

        foreach ($this->source->offersPublishedSince($command->since) as $offer) {
            ($this->publishJob)($this->toPublishJob($offer));
            $seenOfferIds[] = $offer->offerId;
        }

        return $seenOfferIds;
    }

    private function toPublishJob(IncomingJobOffer $offer): PublishJob
    {
        return new PublishJob(
            jobId: $offer->offerId,
            title: $offer->title,
            description: $offer->description,
            companyName: $offer->companyName,
            contractType: $offer->contractType,
            applyUrl: $offer->applyUrl,
            publishedAt: $offer->publishedAt,
            companyLogoUrl: $offer->companyLogoUrl,
            companyDescription: $offer->companyDescription,
            locationLabel: $offer->locationLabel,
            locationInseeCode: $offer->locationInseeCode,
            locationPostalCode: $offer->locationPostalCode,
            romeCode: $offer->romeCode,
            romeLabel: $offer->romeLabel,
            experienceRequirement: $offer->experienceRequirement,
            isWorkStudy: $offer->isWorkStudy,
            salaryDescription: $offer->salaryDescription,
            numberOfPositions: $offer->numberOfPositions,
            skills: \array_map($this->toPublishJobSkill(...), $offer->skills),
            updatedAt: $offer->updatedAt,
        );
    }

    private function toPublishJobSkill(IncomingJobSkill $skill): PublishJobSkill
    {
        return new PublishJobSkill($skill->label, $skill->romeCompetencyCode, $skill->isRequired);
    }
}
