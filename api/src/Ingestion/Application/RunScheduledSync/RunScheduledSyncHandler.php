<?php

declare(strict_types=1);

namespace Ingestion\Application\RunScheduledSync;

use Ingestion\Application\ExpireDelistedOffers\ExpireDelistedOffers;
use Ingestion\Application\ExpireDelistedOffers\ExpireDelistedOffersHandler;
use Ingestion\Application\SyncJobOffers\SyncJobOffers;
use Ingestion\Application\SyncJobOffers\SyncJobOffersHandler;

/**
 * The scheduled entry point (license §7.1: at least once every 24h).
 *
 * Uses a wide lookback window rather than "since the last run": expiry
 * reconciliation (§7.2) needs the *full* currently-active offer set to diff
 * against, not just what's new since last time - a short window would see
 * only recent offers and wrongly expire everything older that's still live.
 * This assumes the offers this app tracks fit within France Travail's
 * 1150-results-per-query ceiling; once real search criteria (ROME codes,
 * keywords) scope the tracked universe, this still holds because those
 * criteria bound the result count, not this window.
 */
final class RunScheduledSyncHandler
{
    private const int LOOKBACK_DAYS = 90;

    public function __construct(
        private readonly SyncJobOffersHandler $syncJobOffers,
        private readonly ExpireDelistedOffersHandler $expireDelistedOffers,
    ) {
    }

    public function __invoke(): void
    {
        $since = new \DateTimeImmutable(\sprintf('-%d days', self::LOOKBACK_DAYS));

        $seenOfferIds = ($this->syncJobOffers)(new SyncJobOffers($since));

        ($this->expireDelistedOffers)(new ExpireDelistedOffers($seenOfferIds));
    }
}
