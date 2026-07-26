<?php

declare(strict_types=1);

namespace Search\Domain;

final readonly class SearchQuery
{
    public function __construct(
        public string $freeText,
        public ?string $contractType = null,
        public ?string $romeCode = null,
    ) {}
}
