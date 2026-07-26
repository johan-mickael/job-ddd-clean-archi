<?php

declare(strict_types=1);

namespace Catalog\Domain;

use Catalog\Domain\Exception\JobNotFound;

interface JobRepositoryInterface
{
    /**
     * @throws JobNotFound
     */
    public function ofId(JobId $id): Job;

    public function add(Job $job): void;

    /**
     * @return list<JobId>
     */
    public function publishedJobIds(): array;
}
