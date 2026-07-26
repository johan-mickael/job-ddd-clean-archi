<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\FranceTravail;

final readonly class SearchResultPage
{
    /**
     * @param list<array<string, mixed>> $rawOffers
     */
    public function __construct(
        public array $rawOffers,
        public int $lastIndex,
        public int $total,
    ) {}

    public function isLastPage(): bool
    {
        return $this->rawOffers === [] || $this->lastIndex >= $this->total - 1;
    }
}
