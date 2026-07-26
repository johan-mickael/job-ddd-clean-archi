<?php

declare(strict_types=1);

namespace Tests\Search;

use Search\Domain\JobSearchDocument;
use Search\Domain\JobSearchIndexInterface;
use Search\Domain\SearchQuery;
use Search\Domain\SearchResultItem;

final class FakeJobSearchIndex implements JobSearchIndexInterface
{
    /** @var array<string, JobSearchDocument> */
    private array $documents = [];

    public function index(JobSearchDocument $document): void
    {
        $this->documents[$document->jobId] = $document;
    }

    public function find(string $jobId): ?JobSearchDocument
    {
        return $this->documents[$jobId] ?? null;
    }

    public function delete(string $jobId): void
    {
        unset($this->documents[$jobId]);
    }

    public function search(SearchQuery $query, array $queryEmbedding): array
    {
        return \array_values(\array_map(
            static fn(JobSearchDocument $document): SearchResultItem => new SearchResultItem(
                $document->jobId,
                $document->title,
                $document->companyName,
                $document->locationLabel,
                $document->applyUrl,
                1.0,
            ),
            $this->documents,
        ));
    }
}
