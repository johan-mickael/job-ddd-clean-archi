<?php

declare(strict_types=1);

namespace CandidateProfile\Infrastructure\Persistence\Doctrine;

use CandidateProfile\Domain\CandidateLanguage;
use CandidateProfile\Domain\CandidateProfile;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateSkill;
use CandidateProfile\Domain\LanguageProficiency;
use CandidateProfile\Domain\SeniorityLevel;

final class CandidateProfileMapper
{
    public function toNewEntity(CandidateProfile $profile): CandidateProfileEntity
    {
        $entity = new CandidateProfileEntity();
        $entity->id = $profile->id->value;
        $this->fill($entity, $profile);

        return $entity;
    }

    public function toDomain(CandidateProfileEntity $entity): CandidateProfile
    {
        return CandidateProfile::extractedFrom(
            id: new CandidateProfileId($entity->id),
            sourceFileName: $entity->sourceFileName,
            extractedAt: $entity->extractedAt,
            seniority: SeniorityLevel::from($entity->seniority),
            domains: $entity->domains,
            roles: $entity->roles,
            languages: \array_map($this->languageToDomain(...), $entity->languages),
            skills: \array_map($this->skillToDomain(...), $entity->skills),
        );
    }

    private function fill(CandidateProfileEntity $entity, CandidateProfile $profile): void
    {
        $entity->sourceFileName = $profile->sourceFileName;
        $entity->extractedAt = $profile->extractedAt;
        $entity->seniority = $profile->seniority->value;
        $entity->domains = $profile->domains;
        $entity->roles = $profile->roles;
        $entity->languages = \array_map($this->languageToArray(...), $profile->languages);
        $entity->skills = \array_map($this->skillToArray(...), $profile->skills);
    }

    /**
     * @return array{name: string, proficiency: string}
     */
    private function languageToArray(CandidateLanguage $language): array
    {
        return ['name' => $language->name, 'proficiency' => $language->proficiency->value];
    }

    /**
     * @param array{name: string, proficiency: string} $language
     */
    private function languageToDomain(array $language): CandidateLanguage
    {
        return new CandidateLanguage($language['name'], LanguageProficiency::from($language['proficiency']));
    }

    /**
     * @return array{label: string, yearsOfExperience: ?int}
     */
    private function skillToArray(CandidateSkill $skill): array
    {
        return ['label' => $skill->label, 'yearsOfExperience' => $skill->yearsOfExperience];
    }

    /**
     * @param array{label: string, yearsOfExperience: ?int} $skill
     */
    private function skillToDomain(array $skill): CandidateSkill
    {
        return new CandidateSkill($skill['label'], $skill['yearsOfExperience']);
    }
}
