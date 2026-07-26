<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\FranceTravail;

use Ingestion\Domain\Exception\JobOfferSourceUnavailable;
use Ingestion\Domain\IncomingJobOffer;
use Ingestion\Domain\JobOfferSource;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FranceTravailJobOfferSource implements JobOfferSource
{
    // France Travail's own limits (verified against the archived official
    // doc): at most 150 results per call, first index capped at 1000.
    private const int PAGE_SIZE = 150;
    private const int MAX_FIRST_INDEX = 1000;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly AccessTokenProvider $tokenProvider,
        private readonly JobOfferPayloadTranslator $translator,
        private readonly string $apiBaseUrl,
        private readonly SearchCriteria $criteria,
    ) {}

    /**
     * @return iterable<IncomingJobOffer>
     */
    public function offersPublishedSince(\DateTimeImmutable $since): iterable
    {
        $until = new \DateTimeImmutable('now');
        $firstIndex = 0;

        do {
            $page = $this->fetchPage($since, $until, $firstIndex);

            foreach ($page->rawOffers as $rawOffer) {
                yield $this->translator->translate($rawOffer);
            }

            $firstIndex += self::PAGE_SIZE;
        } while (!$page->isLastPage() && $firstIndex <= self::MAX_FIRST_INDEX);
    }

    private function fetchPage(\DateTimeImmutable $since, \DateTimeImmutable $until, int $firstIndex): SearchResultPage
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiBaseUrl . '/offres/search', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->tokenProvider->provideToken()->value,
                ],
                'query' => [
                    ...$this->criteria->toQueryParams(),
                    'range' => \sprintf('%d-%d', $firstIndex, $firstIndex + self::PAGE_SIZE - 1),
                    'minCreationDate' => $since->format('Y-m-d\TH:i:s\Z'),
                    'maxCreationDate' => $until->format('Y-m-d\TH:i:s\Z'),
                ],
            ]);

            /** @var list<array<string, mixed>> $rawOffers */
            $rawOffers = $response->toArray()['resultats'] ?? [];
            $contentRange = $response->getHeaders()['content-range'][0] ?? '';
        } catch (\Throwable $e) {
            throw JobOfferSourceUnavailable::dueTo($e);
        }

        return new SearchResultPage($rawOffers, ...$this->parseContentRange($contentRange, $firstIndex, \count($rawOffers)));
    }

    /**
     * @return array{lastIndex: int, total: int}
     */
    private function parseContentRange(string $header, int $firstIndex, int $offerCount): array
    {
        if (\preg_match('/offres (\d+)-(\d+)\/(\d+)/', $header, $matches) === 1) {
            return ['lastIndex' => (int) $matches[2], 'total' => (int) $matches[3]];
        }

        $lastIndex = $firstIndex + $offerCount - 1;

        return ['lastIndex' => $lastIndex, 'total' => $lastIndex + 1];
    }
}
