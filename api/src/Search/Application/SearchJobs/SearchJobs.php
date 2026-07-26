<?php

declare(strict_types=1);

namespace Search\Application\SearchJobs;

final readonly class SearchJobs
{
    public function __construct(
        public string $freeText,
        public ?string $contractType = null,
        public ?string $romeCode = null,
    ) {}
}
