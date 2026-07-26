<?php

declare(strict_types=1);

namespace Catalog\Domain;

use Shared\Domain\RomeCompetencyCode;

final readonly class JobSkill
{
    public function __construct(
        public string $label,
        public ?RomeCompetencyCode $romeCompetencyCode,
        public bool $isRequired,
    ) {}
}
