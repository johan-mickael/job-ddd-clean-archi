<?php

declare(strict_types=1);

namespace Catalog\Application\GetJob;

final readonly class JobViewSkill
{
    public function __construct(
        public string $label,
        public ?string $romeCompetencyCode,
        public bool $isRequired,
    ) {}
}
