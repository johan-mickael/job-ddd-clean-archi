<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\FranceTravail;

// Scopes which offers this deployment tracks. Not optional in practice:
// an unscoped search hits France Travail's 1150-results-per-query ceiling
// fast (300k+ live offers nationwide). At least one of keywords/romeCodes/
// commune should be set.
final readonly class SearchCriteria
{
    /**
     * @param list<string> $romeCodes at most 3 (France Travail's own limit)
     */
    public function __construct(
        public ?string $keywords = null,
        public array $romeCodes = [],
        public ?string $commune = null,
        public ?int $distanceKm = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->keywords !== null && $this->keywords !== '') {
            $params['motsCles'] = $this->keywords;
        }

        $romeCodes = \array_values(\array_filter($this->romeCodes, static fn (string $code): bool => $code !== ''));
        if ($romeCodes !== []) {
            $params['codeROME'] = \implode(',', \array_slice($romeCodes, 0, 3));
        }

        if ($this->commune !== null && $this->commune !== '') {
            $params['commune'] = $this->commune;

            if ($this->distanceKm !== null) {
                $params['distance'] = (string) $this->distanceKm;
            }
        }

        return $params;
    }
}
