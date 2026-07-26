<?php

declare(strict_types=1);

namespace Catalog\Application\ExpireJob;

use Catalog\Domain\JobId;
use Catalog\Domain\JobRepositoryInterface;

final class ExpireJobHandler implements ExpireJobHandlerInterface
{
    public function __construct(private readonly JobRepositoryInterface $jobs)
    {
    }

    public function __invoke(ExpireJob $command): void
    {
        $job = $this->jobs->ofId(new JobId($command->jobId));
        $job->expire();

        $this->jobs->add($job);
    }
}
