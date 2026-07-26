<?php

declare(strict_types=1);

namespace Shared\IntegrationEvent;

final readonly class JobWasChanged
{
    public function __construct(public string $jobId) {}
}
