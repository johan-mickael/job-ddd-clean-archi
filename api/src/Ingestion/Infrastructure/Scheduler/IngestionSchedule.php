<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

// License §7.1 floor: at least once every 24h. Runs hourly in practice -
// cheap and idempotent (PublishJob upserts), and keeps the reconciliation
// window (RunScheduledSyncHandler) tight without ever risking the 24h floor
// on a missed run.
#[AsSchedule('ingestion')]
final class IngestionSchedule implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(RecurringMessage::every('1 hour', new RunScheduledSyncMessage()));
    }
}
