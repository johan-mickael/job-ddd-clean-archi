<?php

declare(strict_types=1);

namespace Search\Domain;

final readonly class SearchResults
{
    /**
     * @param list<SearchResultItem> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $pageSize,
        public int $total,
    ) {}

    public function totalPages(): int
    {
        return $this->total === 0 ? 0 : (int) \ceil($this->total / $this->pageSize);
    }
}
