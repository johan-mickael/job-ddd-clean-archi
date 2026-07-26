<?php

declare(strict_types=1);

namespace Catalog\Domain;

use Catalog\Domain\Exception\InvalidWorkLocation;

final readonly class WorkLocation
{
    public function __construct(
        public string $label,
        public ?string $inseeCode = null,
        public ?string $postalCode = null,
    ) {
        if (\trim($label) === '') {
            throw InvalidWorkLocation::blankLabel();
        }
    }
}
