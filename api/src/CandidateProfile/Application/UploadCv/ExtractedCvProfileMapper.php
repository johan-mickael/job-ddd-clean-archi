<?php

declare(strict_types=1);

namespace CandidateProfile\Application\UploadCv;

use CandidateProfile\Domain\CandidateLanguage;
use CandidateProfile\Domain\CandidateProfile;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateSkill;
use CandidateProfile\Domain\Exception\InvalidCvProfileData;
use CandidateProfile\Domain\LanguageProficiency;
use CandidateProfile\Domain\SeniorityLevel;

/**
 * The untrusted-LLM-JSON validation boundary: nothing from $raw reaches a
 * CandidateProfile without passing through here first, and every failure
 * mode - wrong type, missing key, unknown enum value - is rejected with a
 * specific field name rather than silently coerced or defaulted.
 */
final class ExtractedCvProfileMapper
{
    /**
     * @param array<string, mixed> $raw
     */
    public function toCandidateProfile(
        array $raw,
        CandidateProfileId $id,
        string $sourceFileName,
        \DateTimeImmutable $extractedAt,
    ): CandidateProfile {
        return CandidateProfile::extractedFrom(
            id: $id,
            sourceFileName: $sourceFileName,
            extractedAt: $extractedAt,
            seniority: $this->seniority($raw),
            domains: $this->stringList($raw, 'domains'),
            roles: $this->stringList($raw, 'roles'),
            languages: $this->languages($raw),
            skills: $this->skills($raw),
        );
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function seniority(array $raw): SeniorityLevel
    {
        $value = $raw['seniority'] ?? null;

        if (!\is_string($value)) {
            throw InvalidCvProfileData::becauseOfField('seniority', 'must be a string.');
        }

        return SeniorityLevel::tryFrom($value)
            ?? throw InvalidCvProfileData::becauseOfField('seniority', \sprintf('"%s" is not a recognized seniority level.', $value));
    }

    /**
     * @param array<string, mixed> $raw
     *
     * @return list<string>
     */
    private function stringList(array $raw, string $field): array
    {
        $value = $raw[$field] ?? null;

        if (!\is_array($value)) {
            throw InvalidCvProfileData::becauseOfField($field, 'must be an array.');
        }

        $strings = [];

        foreach ($value as $entry) {
            if (!\is_string($entry)) {
                throw InvalidCvProfileData::becauseOfField($field, 'every entry must be a string.');
            }

            $strings[] = $entry;
        }

        return $strings;
    }

    /**
     * @param array<string, mixed> $raw
     *
     * @return list<CandidateLanguage>
     */
    private function languages(array $raw): array
    {
        $value = $raw['languages'] ?? [];

        if (!\is_array($value)) {
            throw InvalidCvProfileData::becauseOfField('languages', 'must be an array.');
        }

        return \array_map($this->language(...), \array_values($value));
    }

    private function language(mixed $entry): CandidateLanguage
    {
        if (!\is_array($entry) || !\is_string($entry['name'] ?? null) || !\is_string($entry['proficiency'] ?? null)) {
            throw InvalidCvProfileData::becauseOfField('languages[]', 'must have string "name" and "proficiency" fields.');
        }

        $proficiency = LanguageProficiency::tryFrom($entry['proficiency'])
            ?? throw InvalidCvProfileData::becauseOfField('languages[].proficiency', \sprintf('"%s" is not a recognized proficiency.', $entry['proficiency']));

        return new CandidateLanguage($entry['name'], $proficiency);
    }

    /**
     * @param array<string, mixed> $raw
     *
     * @return list<CandidateSkill>
     */
    private function skills(array $raw): array
    {
        $value = $raw['skills'] ?? [];

        if (!\is_array($value)) {
            throw InvalidCvProfileData::becauseOfField('skills', 'must be an array.');
        }

        return \array_map($this->skill(...), \array_values($value));
    }

    private function skill(mixed $entry): CandidateSkill
    {
        if (!\is_array($entry) || !\is_string($entry['label'] ?? null)) {
            throw InvalidCvProfileData::becauseOfField('skills[]', 'must have a string "label" field.');
        }

        $years = $entry['yearsOfExperience'] ?? null;

        if ($years !== null && !\is_int($years)) {
            throw InvalidCvProfileData::becauseOfField('skills[].yearsOfExperience', 'must be an integer or null.');
        }

        return new CandidateSkill($entry['label'], $years);
    }
}
