<?php

declare(strict_types=1);

namespace Search\Application\SearchJobs;

use Search\Domain\SearchResults;

interface SearchJobsHandlerInterface
{
    public function __invoke(SearchJobs $query): SearchResults;
}
