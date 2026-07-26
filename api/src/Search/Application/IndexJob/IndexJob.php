<?php

declare(strict_types=1);

namespace Search\Application\IndexJob;

final readonly class IndexJob
{
    public function __construct(public string $jobId) {}
}
