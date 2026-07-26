<?php

declare(strict_types=1);

namespace Matching\Domain;

/**
 * How a candidate's profile becomes the free-text half of a job search -
 * the one piece of matching policy this context owns so far. Roles carry
 * the query (what the candidate does), skills narrow it (what they bring).
 * Domains are intentionally left out: they're broad enough ("backend") to
 * dilute the semantic query more than they focus it.
 */
final readonly class CandidateSearchPhrase
{
    public string $value;

    /**
     * @param list<string> $roles
     * @param list<string> $skills
     */
    public function __construct(array $roles, array $skills)
    {
        $parts = [];

        if ($roles !== []) {
            $parts[] = \implode(', ', $roles);
        }

        if ($skills !== []) {
            $parts[] = 'Skills: ' . \implode(', ', $skills);
        }

        $this->value = \implode('. ', $parts);
    }
}
