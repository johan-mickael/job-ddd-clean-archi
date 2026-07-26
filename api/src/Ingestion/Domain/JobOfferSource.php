<?php

declare(strict_types=1);

namespace Ingestion\Domain;

interface JobOfferSource
{
    /**
     * @return iterable<IncomingJobOffer>
     */
    public function offersPublishedSince(\DateTimeImmutable $since): iterable;
}
