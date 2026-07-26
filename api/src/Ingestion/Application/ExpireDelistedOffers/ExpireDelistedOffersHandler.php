<?php

declare(strict_types=1);

namespace Ingestion\Application\ExpireDelistedOffers;

use Catalog\Application\ExpireJob\ExpireJob;
use Catalog\Application\ExpireJob\ExpireJobHandlerInterface;
use Catalog\Application\ListPublishedJobIds\ListPublishedJobIdsHandlerInterface;

// License §7.2: offers absent from a sync pass must be expired, every sync -
// this is what keeps our copy from showing offers France Travail has delisted.
final class ExpireDelistedOffersHandler
{
    public function __construct(
        private readonly ListPublishedJobIdsHandlerInterface $listPublishedJobIds,
        private readonly ExpireJobHandlerInterface $expireJob,
    ) {
    }

    public function __invoke(ExpireDelistedOffers $command): void
    {
        $delistedIds = \array_diff(($this->listPublishedJobIds)(), $command->seenOfferIds);

        foreach ($delistedIds as $jobId) {
            ($this->expireJob)(new ExpireJob($jobId));
        }
    }
}
