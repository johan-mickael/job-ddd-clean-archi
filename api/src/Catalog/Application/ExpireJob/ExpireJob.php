<?php

declare(strict_types=1);

namespace Catalog\Application\ExpireJob;

final readonly class ExpireJob
{
    public function __construct(public string $jobId)
    {
    }
}
