<?php

declare(strict_types=1);

namespace Matching\Presentation\Http;

use Matching\Application\MatchJobsToProfile\MatchedJobs;
use Matching\Application\MatchJobsToProfile\MatchedJobView;
use Matching\Application\MatchJobsToProfile\MatchJobsToProfile;
use Matching\Application\MatchJobsToProfile\MatchJobsToProfileHandlerInterface;
use Shared\Domain\CurrentUserIdProviderInterface;
use Shared\Domain\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class MatchJobsController
{
    private const int MAX_PAGE_SIZE = 50;

    public function __construct(
        private readonly MatchJobsToProfileHandlerInterface $matchJobsToProfile,
        private readonly CurrentUserIdProviderInterface $currentUserId,
    ) {}

    #[Route('/api/cv/{candidateProfileId}/matches', name: 'candidate_profile_matches', methods: ['GET'])]
    public function __invoke(string $candidateProfileId, Request $request): JsonResponse
    {
        try {
            $matches = ($this->matchJobsToProfile)(new MatchJobsToProfile(
                candidateProfileId: $candidateProfileId,
                requesterId: $this->currentUserId->id(),
                page: \max(1, $request->query->getInt('page', 1)),
                pageSize: \min(self::MAX_PAGE_SIZE, \max(1, $request->query->getInt('pageSize', 20))),
            ));
        } catch (DomainException) {
            // Crossing into CandidateProfile's Application layer (the sanctioned
            // cross-context port) can only surface its failures through the
            // shared domain-exception marker here - Presentation may not
            // reach into another context's Domain to catch its concrete type.
            return new JsonResponse(['error' => 'Candidate profile not found.'], 404);
        }

        return new JsonResponse($this->toArray($matches));
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(MatchedJobs $matches): array
    {
        return [
            'results' => \array_map($this->itemToArray(...), $matches->items),
            'page' => $matches->page,
            'pageSize' => $matches->pageSize,
            'total' => $matches->total,
            'totalPages' => $matches->totalPages,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function itemToArray(MatchedJobView $item): array
    {
        return [
            'jobId' => $item->jobId,
            'title' => $item->title,
            'companyName' => $item->companyName,
            'locationLabel' => $item->locationLabel,
            'applyUrl' => $item->applyUrl,
            'publishedAt' => $item->publishedAt->format(\DATE_ATOM),
            'score' => $item->score,
        ];
    }
}
