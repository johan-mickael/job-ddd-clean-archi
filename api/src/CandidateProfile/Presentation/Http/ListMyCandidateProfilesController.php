<?php

declare(strict_types=1);

namespace CandidateProfile\Presentation\Http;

use CandidateProfile\Application\GetCandidateProfile\CandidateProfileView;
use CandidateProfile\Application\ListMyCandidateProfiles\ListMyCandidateProfiles;
use CandidateProfile\Application\ListMyCandidateProfiles\ListMyCandidateProfilesHandlerInterface;
use Shared\Domain\CurrentUserIdProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListMyCandidateProfilesController
{
    public function __construct(
        private readonly ListMyCandidateProfilesHandlerInterface $listMyCandidateProfiles,
        private readonly CurrentUserIdProviderInterface $currentUserId,
    ) {}

    #[Route('/api/cv', name: 'candidate_profile_list_mine', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $profiles = ($this->listMyCandidateProfiles)(new ListMyCandidateProfiles($this->currentUserId->id()));

        return new JsonResponse(['profiles' => \array_map($this->toArray(...), $profiles)]);
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
        ];
    }
}
