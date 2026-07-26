<?php

declare(strict_types=1);

namespace CandidateProfile\Application\GetCandidateProfile;

final readonly class CandidateProfileView
{
    /**
     * @param list<string>                       $domains
     * @param list<string>                       $roles
     * @param list<CandidateProfileLanguageView>  $languages
     * @param list<CandidateProfileSkillView>     $skills
     */
    public function __construct(
        public string $candidateProfileId,
        public string $sourceFileName,
        public \DateTimeImmutable $extractedAt,
        public string $seniority,
        public array $domains,
        public array $roles,
        public array $languages,
        public array $skills,
    ) {}
}
