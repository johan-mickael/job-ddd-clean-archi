<?php

declare(strict_types=1);

namespace Catalog\Infrastructure\Messenger;

use Catalog\Application\EventPublisherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerEventPublisher implements EventPublisherInterface
{
    public function __construct(private readonly MessageBusInterface $bus) {}

    public function publish(object $event): void
    {
        $this->bus->dispatch($event);
    }
}
