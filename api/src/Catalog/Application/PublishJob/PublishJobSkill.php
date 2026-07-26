<?php

declare(strict_types=1);

namespace Catalog\Application\PublishJob;

final readonly class PublishJobSkill
{
    public function __construct(
        public string $label,
        public ?string $romeCompetencyCode,
        public bool $isRequired,
    ) {
    }
}
