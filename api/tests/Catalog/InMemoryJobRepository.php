<?php

declare(strict_types=1);

namespace Tests\Catalog;

use Catalog\Domain\Exception\JobNotFound;
use Catalog\Domain\Job;
use Catalog\Domain\JobId;
use Catalog\Domain\JobRepositoryInterface;

final class InMemoryJobRepository implements JobRepositoryInterface
{
    /** @var array<string, Job> */
    private array $jobs = [];

    public function ofId(JobId $id): Job
    {
        return $this->jobs[$id->value] ?? throw JobNotFound::withId($id);
    }

    public function add(Job $job): void
    {
        $this->jobs[$job->id->value] = $job;
    }

    public function publishedJobIds(): array
    {
        return \array_values(\array_map(
            static fn(Job $job): JobId => $job->id,
            \array_filter($this->jobs, static fn(Job $job): bool => !$job->isExpired()),
        ));
    }
}
