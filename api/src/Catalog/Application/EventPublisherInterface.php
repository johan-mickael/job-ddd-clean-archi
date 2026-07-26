<?php

declare(strict_types=1);

namespace Catalog\Application;

interface EventPublisherInterface
{
    public function publish(object $event): void;
}
