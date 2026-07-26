<?php

declare(strict_types=1);

namespace Search\Application\SearchJobs;

use Search\Domain\EmbeddingProviderInterface;
use Search\Domain\JobSearchIndexInterface;
use Search\Domain\SearchQuery;
use Search\Domain\SearchResults;

final class SearchJobsHandler
{
    public function __construct(
        private readonly JobSearchIndexInterface $index,
        private readonly EmbeddingProviderInterface $embeddings,
    ) {}

    public function __invoke(SearchJobs $query): SearchResults
    {
        $embedding = $this->embeddings->embed($query->freeText);

        return $this->index->search(
            new SearchQuery($query->freeText, $query->contractType, $query->romeCode, $query->page, $query->pageSize),
            $embedding,
        );
    }
}
