<?php

declare(strict_types=1);

namespace Tests\Catalog;

use Catalog\Application\EventPublisherInterface;

final class RecordingEventPublisher implements EventPublisherInterface
{
    /** @var list<object> */
    private array $published = [];

    public function publish(object $event): void
    {
        $this->published[] = $event;
    }

    /**
     * @return list<object>
     */
    public function published(): array
    {
        return $this->published;
    }
}
