<?php

declare(strict_types=1);

namespace Ingestion\Domain;

final readonly class IncomingJobSkill
{
    public function __construct(
        public string $label,
        public ?string $romeCompetencyCode,
        public bool $isRequired,
    ) {}
}
