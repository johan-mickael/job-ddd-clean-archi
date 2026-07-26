<?php

declare(strict_types=1);

namespace Search\Application\ReindexAllJobs;

use Catalog\Application\ListPublishedJobIds\ListPublishedJobIdsHandlerInterface;
use Search\Application\IndexJob\IndexJob;
use Search\Application\IndexJob\IndexJobHandler;

final class ReindexAllJobsHandler
{
    public function __construct(
        private readonly ListPublishedJobIdsHandlerInterface $listPublishedJobIds,
        private readonly IndexJobHandler $indexJob,
    ) {}

    public function __invoke(ReindexAllJobs $command): int
    {
        $jobIds = ($this->listPublishedJobIds)();

        foreach ($jobIds as $jobId) {
            ($this->indexJob)(new IndexJob($jobId));
        }

        return \count($jobIds);
    }
}
