<?php

declare(strict_types=1);

namespace CandidateProfile\Domain;

use CandidateProfile\Domain\Exception\InvalidCvProfileData;

final readonly class CandidateProfile
{
    /**
     * @param list<string>            $domains
     * @param list<string>            $roles
     * @param list<CandidateLanguage> $languages
     * @param list<CandidateSkill>    $skills
     */
    private function __construct(
        public CandidateProfileId $id,
        public string $sourceFileName,
        public \DateTimeImmutable $extractedAt,
        public SeniorityLevel $seniority,
        public array $domains,
        public array $roles,
        public array $languages,
        public array $skills,
    ) {}

    /**
     * @param list<string>            $domains
     * @param list<string>            $roles
     * @param list<CandidateLanguage> $languages
     * @param list<CandidateSkill>    $skills
     */
    public static function extractedFrom(
        CandidateProfileId $id,
        string $sourceFileName,
        \DateTimeImmutable $extractedAt,
        SeniorityLevel $seniority,
        array $domains,
        array $roles,
        array $languages,
        array $skills,
    ): self {
        if (\trim($sourceFileName) === '') {
            throw InvalidCvProfileData::becauseOfField('sourceFileName', 'must not be blank.');
        }

        // domains (business/industry domains) may legitimately come back
        // empty for a short or skills-focused CV - roles may not, since a
        // profile identifying no role at all isn't useful for matching.
        $domains = self::normalizedStrings($domains, 'domains');
        $roles = self::normalizedStrings($roles, 'roles');

        if ($roles === []) {
            throw InvalidCvProfileData::becauseOfField('roles', 'must contain at least one entry.');
        }

        return new self($id, $sourceFileName, $extractedAt, $seniority, $domains, $roles, $languages, $skills);
    }

    /**
     * @param list<mixed> $values
     *
     * @return list<string>
     */
    private static function normalizedStrings(array $values, string $field): array
    {
        $normalized = [];

        foreach ($values as $value) {
            if (!\is_string($value)) {
                throw InvalidCvProfileData::becauseOfField($field, 'every entry must be a string.');
            }

            $trimmed = \trim($value);

            if ($trimmed !== '') {
                $normalized[] = $trimmed;
            }
        }

        return \array_values(\array_unique($normalized));
    }
}
