<?php

declare(strict_types=1);

namespace Catalog\Application\ListPublishedJobIds;

use Catalog\Domain\JobId;
use Catalog\Domain\JobRepositoryInterface;

final class ListPublishedJobIdsHandler implements ListPublishedJobIdsHandlerInterface
{
    public function __construct(private readonly JobRepositoryInterface $jobs) {}

    /**
     * @return list<string>
     */
    public function __invoke(): array
    {
        return \array_map(
            static fn(JobId $jobId): string => $jobId->value,
            $this->jobs->publishedJobIds(),
        );
    }
}
