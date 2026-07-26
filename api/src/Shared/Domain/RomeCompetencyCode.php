<?php

declare(strict_types=1);

namespace Shared\Domain;

use Shared\Domain\Exception\InvalidRomeCompetencyCode;

// Distinct taxonomy from RomeCode (ROME occupation codes, e.g. "M1805"):
// France Travail documents competency codes only as "String 6" with no
// published pattern, so this stays a presence check rather than a guessed
// regex that would reject legitimate codes.
final readonly class RomeCompetencyCode
{
    public function __construct(public string $value)
    {
        if (\trim($value) === '') {
            throw InvalidRomeCompetencyCode::blank();
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
