<?php

declare(strict_types=1);

namespace CandidateProfile\Application\GetCandidateProfile;

use CandidateProfile\Domain\CandidateLanguage;
use CandidateProfile\Domain\CandidateProfile;
use CandidateProfile\Domain\CandidateProfileId;
use CandidateProfile\Domain\CandidateProfileRepositoryInterface;
use CandidateProfile\Domain\CandidateSkill;

final class GetCandidateProfileHandler implements GetCandidateProfileHandlerInterface
{
    public function __construct(private readonly CandidateProfileRepositoryInterface $profiles) {}

    public function __invoke(GetCandidateProfile $query): CandidateProfileView
    {
        return $this->toView($this->profiles->ofId(new CandidateProfileId($query->candidateProfileId)));
    }

    private function toView(CandidateProfile $profile): CandidateProfileView
    {
        return new CandidateProfileView(
            candidateProfileId: $profile->id->value,
            sourceFileName: $profile->sourceFileName,
            extractedAt: $profile->extractedAt,
            seniority: $profile->seniority->value,
            domains: $profile->domains,
            roles: $profile->roles,
            languages: \array_map($this->languageView(...), $profile->languages),
            skills: \array_map($this->skillView(...), $profile->skills),
        );
    }

    private function languageView(CandidateLanguage $language): CandidateProfileLanguageView
    {
        return new CandidateProfileLanguageView($language->name, $language->proficiency->value);
    }

    private function skillView(CandidateSkill $skill): CandidateProfileSkillView
    {
        return new CandidateProfileSkillView($skill->label, $skill->yearsOfExperience);
    }
}
