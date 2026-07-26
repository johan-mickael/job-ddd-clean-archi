<?php

declare(strict_types=1);

namespace Tests\Ingestion\Application\SyncJobOffers;

use Ingestion\Domain\IncomingJobOffer;
use Ingestion\Domain\JobOfferSource;

final class FakeJobOfferSource implements JobOfferSource
{
    /**
     * @param list<IncomingJobOffer> $offers
     */
    public function __construct(private readonly array $offers) {}

    public function offersPublishedSince(\DateTimeImmutable $since): iterable
    {
        yield from $this->offers;
    }
}
