<?php

declare(strict_types=1);

namespace Tests\Matching;

use Search\Application\SearchJobs\SearchJobs;
use Search\Application\SearchJobs\SearchJobsHandlerInterface;
use Search\Domain\SearchResults;

final class FakeSearchJobsHandler implements SearchJobsHandlerInterface
{
    public ?SearchJobs $lastQuery = null;

    public function __construct(private readonly SearchResults $results) {}

    public function __invoke(SearchJobs $query): SearchResults
    {
        $this->lastQuery = $query;

        return $this->results;
    }
}
