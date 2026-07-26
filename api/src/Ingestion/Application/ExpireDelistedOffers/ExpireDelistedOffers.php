<?php

declare(strict_types=1);

namespace Ingestion\Application\ExpireDelistedOffers;

final readonly class ExpireDelistedOffers
{
    /**
     * @param list<string> $seenOfferIds offer ids witnessed on this sync pass
     */
    public function __construct(public array $seenOfferIds) {}
}
