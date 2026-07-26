<?php

declare(strict_types=1);

namespace Search\Infrastructure\Messenger;

use Search\Application\IndexJob\IndexJob;
use Search\Application\IndexJob\IndexJobHandler;
use Shared\IntegrationEvent\JobWasChanged;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class JobWasChangedMessageHandler
{
    public function __construct(private readonly IndexJobHandler $indexJob) {}

    public function __invoke(JobWasChanged $event): void
    {
        ($this->indexJob)(new IndexJob($event->jobId));
    }
}
