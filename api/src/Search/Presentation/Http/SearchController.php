<?php

declare(strict_types=1);

namespace Search\Presentation\Http;

use Search\Application\SearchJobs\SearchJobs;
use Search\Application\SearchJobs\SearchJobsHandler;
use Search\Domain\SearchResultItem;
use Search\Domain\SearchResults;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController
{
    private const int MAX_PAGE_SIZE = 50;

    public function __construct(private readonly SearchJobsHandler $searchJobs) {}

    #[Route('/api/search', name: 'search_jobs', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $results = ($this->searchJobs)(new SearchJobs(
            freeText: $request->query->getString('q'),
            contractType: $request->query->get('contractType'),
            romeCode: $request->query->get('romeCode'),
            page: \max(1, $request->query->getInt('page', 1)),
            pageSize: \min(self::MAX_PAGE_SIZE, \max(1, $request->query->getInt('pageSize', 20))),
        ));

        return new JsonResponse($this->toArray($results));
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(SearchResults $results): array
    {
        return [
            'results' => \array_map($this->itemToArray(...), $results->items),
            'page' => $results->page,
            'pageSize' => $results->pageSize,
            'total' => $results->total,
            'totalPages' => $results->totalPages(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function itemToArray(SearchResultItem $item): array
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
