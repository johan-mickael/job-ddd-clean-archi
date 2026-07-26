<?php

declare(strict_types=1);

namespace Search\Presentation\Http;

use Search\Application\SearchJobs\SearchJobs;
use Search\Application\SearchJobs\SearchJobsHandler;
use Search\Domain\SearchResultItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController
{
    public function __construct(private readonly SearchJobsHandler $searchJobs) {}

    #[Route('/api/search', name: 'search_jobs', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $results = ($this->searchJobs)(new SearchJobs(
            freeText: $request->query->getString('q'),
            contractType: $request->query->get('contractType'),
            romeCode: $request->query->get('romeCode'),
        ));

        return new JsonResponse(['results' => \array_map($this->toArray(...), $results)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(SearchResultItem $item): array
    {
        return [
            'jobId' => $item->jobId,
            'title' => $item->title,
            'companyName' => $item->companyName,
            'locationLabel' => $item->locationLabel,
            'applyUrl' => $item->applyUrl,
            'score' => $item->score,
        ];
    }
}
