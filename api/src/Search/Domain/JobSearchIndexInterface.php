<?php

declare(strict_types=1);

namespace Search\Domain;

use Search\Domain\Exception\JobSearchIndexUnavailable;

interface JobSearchIndexInterface
{
    /**
     * @throws JobSearchIndexUnavailable
     */
    public function index(JobSearchDocument $document): void;

    /**
     * @throws JobSearchIndexUnavailable
     */
    public function find(string $jobId): ?JobSearchDocument;

    /**
     * @throws JobSearchIndexUnavailable
     */
    public function delete(string $jobId): void;

    /**
     * @param list<float> $queryEmbedding
     *
     * @throws JobSearchIndexUnavailable
     */
    public function search(SearchQuery $query, array $queryEmbedding): SearchResults;
}
