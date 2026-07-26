<?php

declare(strict_types=1);

namespace Catalog\Application\GetJob;

final readonly class GetJob
{
    public function __construct(public string $jobId) {}
}
