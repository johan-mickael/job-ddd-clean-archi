<?php

declare(strict_types=1);

namespace CandidateProfile\Presentation\Http;

use CandidateProfile\Application\GetCandidateProfile\CandidateProfileLanguageView;
use CandidateProfile\Application\GetCandidateProfile\CandidateProfileSkillView;
use CandidateProfile\Application\GetCandidateProfile\CandidateProfileView;
use CandidateProfile\Application\GetCandidateProfile\GetCandidateProfile;
use CandidateProfile\Application\GetCandidateProfile\GetCandidateProfileHandlerInterface;
use CandidateProfile\Domain\Exception\CandidateProfileNotFound;
use Shared\Domain\CurrentUserIdProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetCandidateProfileController
{
    public function __construct(
        private readonly GetCandidateProfileHandlerInterface $getCandidateProfile,
        private readonly CurrentUserIdProviderInterface $currentUserId,
    ) {}

    #[Route('/api/cv/{candidateProfileId}', name: 'candidate_profile_detail', methods: ['GET'])]
    public function __invoke(string $candidateProfileId): JsonResponse
    {
        try {
            $view = ($this->getCandidateProfile)(new GetCandidateProfile($candidateProfileId, $this->currentUserId->id()));
        } catch (CandidateProfileNotFound) {
            return new JsonResponse(['error' => 'Candidate profile not found.'], 404);
        }

        return new JsonResponse($this->toArray($view));
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(CandidateProfileView $view): array
    {
        return [
            'candidateProfileId' => $view->candidateProfileId,
            'sourceFileName' => $view->sourceFileName,
            'extractedAt' => $view->extractedAt->format(\DATE_ATOM),
            'seniority' => $view->seniority,
            'domains' => $view->domains,
            'roles' => $view->roles,
            'languages' => \array_map($this->languageToArray(...), $view->languages),
            'skills' => \array_map($this->skillToArray(...), $view->skills),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function languageToArray(CandidateProfileLanguageView $language): array
    {
        return ['name' => $language->name, 'proficiency' => $language->proficiency];
    }

    /**
     * @return array<string, mixed>
     */
    private function skillToArray(CandidateProfileSkillView $skill): array
    {
        return ['label' => $skill->label, 'yearsOfExperience' => $skill->yearsOfExperience];
    }
}
