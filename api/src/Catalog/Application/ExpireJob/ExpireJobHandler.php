<?php

declare(strict_types=1);

namespace Catalog\Application\ExpireJob;

use Catalog\Application\EventPublisherInterface;
use Catalog\Domain\JobId;
use Catalog\Domain\JobRepositoryInterface;
use Shared\IntegrationEvent\JobWasChanged;

final class ExpireJobHandler implements ExpireJobHandlerInterface
{
    public function __construct(
        private readonly JobRepositoryInterface $jobs,
        private readonly EventPublisherInterface $events,
    ) {}

    public function __invoke(ExpireJob $command): void
    {
        $job = $this->jobs->ofId(new JobId($command->jobId));
        $job->expire();

        $this->jobs->add($job);
        $this->events->publish(new JobWasChanged($command->jobId));
    }
}
