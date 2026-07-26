<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\Scheduler;

use Ingestion\Application\RunScheduledSync\RunScheduledSyncHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RunScheduledSyncMessageHandler
{
    public function __construct(private readonly RunScheduledSyncHandler $runScheduledSync) {}

    public function __invoke(RunScheduledSyncMessage $message): void
    {
        ($this->runScheduledSync)();
    }
}
