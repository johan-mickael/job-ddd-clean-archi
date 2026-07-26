<?php

declare(strict_types=1);

namespace Search\Infrastructure\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use Search\Domain\Exception\JobSearchIndexUnavailable;
use Search\Domain\JobSearchDocument;
use Search\Domain\JobSearchIndexInterface;
use Search\Domain\SearchQuery;
use Search\Domain\SearchResultItem;

final class ElasticsearchJobSearchIndex implements JobSearchIndexInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $indexName,
    ) {}

    public function index(JobSearchDocument $document): void
    {
        try {
            $this->client->index([
                'index' => $this->indexName,
                'id' => $document->jobId,
                'body' => $this->toBody($document),
            ]);
        } catch (\Throwable $e) {
            throw JobSearchIndexUnavailable::dueTo($e);
        }
    }

    public function find(string $jobId): ?JobSearchDocument
    {
        try {
            $response = $this->toArray($this->client->get(['index' => $this->indexName, 'id' => $jobId]));
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                return null;
            }

            throw JobSearchIndexUnavailable::dueTo($e);
        } catch (\Throwable $e) {
            throw JobSearchIndexUnavailable::dueTo($e);
        }

        /** @var array{jobId: string, title: string, description: string, companyName: string, contractType: string, applyUrl: string, publishedAt: string, locationLabel?: ?string, romeCode?: ?string, romeLabel?: ?string, salaryDescription?: ?string, isWorkStudy: bool, skillLabels?: list<string>, updatedAt?: ?string, embedding?: ?list<float>} $source */
        $source = $response['_source'];

        return $this->fromSource($source);
    }

    public function delete(string $jobId): void
    {
        try {
            $this->client->delete(['index' => $this->indexName, 'id' => $jobId]);
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                return;
            }

            throw JobSearchIndexUnavailable::dueTo($e);
        } catch (\Throwable $e) {
            throw JobSearchIndexUnavailable::dueTo($e);
        }
    }

    public function search(SearchQuery $query, array $queryEmbedding): array
    {
        try {
            /** @var array{hits?: array{hits?: list<array{_score: float, _source: array{jobId: string, title: string, companyName: string, locationLabel?: ?string, applyUrl: string}}>}} $response */
            $response = $this->toArray($this->client->search([
                'index' => $this->indexName,
                'body' => $this->searchBody($query, $queryEmbedding),
            ]));
        } catch (\Throwable $e) {
            throw JobSearchIndexUnavailable::dueTo($e);
        }

        return \array_map($this->toResultItem(...), $response['hits']['hits'] ?? []);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function toArray(Elasticsearch|Promise $response): array
    {
        if (!$response instanceof Elasticsearch) {
            throw JobSearchIndexUnavailable::dueTo(new \RuntimeException('Unexpected async Elasticsearch response.'));
        }

        return $response->asArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function toBody(JobSearchDocument $document): array
    {
        return [
            'jobId' => $document->jobId,
            'title' => $document->title,
            'description' => $document->description,
            'companyName' => $document->companyName,
            'contractType' => $document->contractType,
            'applyUrl' => $document->applyUrl,
            'publishedAt' => $document->publishedAt->format(\DATE_ATOM),
            'locationLabel' => $document->locationLabel,
            'romeCode' => $document->romeCode,
            'romeLabel' => $document->romeLabel,
            'salaryDescription' => $document->salaryDescription,
            'isWorkStudy' => $document->isWorkStudy,
            'skillLabels' => $document->skillLabels,
            'updatedAt' => $document->updatedAt?->format(\DATE_ATOM),
            'embedding' => $document->embedding,
        ];
    }

    /**
     * @param array{jobId: string, title: string, description: string, companyName: string, contractType: string, applyUrl: string, publishedAt: string, locationLabel?: ?string, romeCode?: ?string, romeLabel?: ?string, salaryDescription?: ?string, isWorkStudy: bool, skillLabels?: list<string>, updatedAt?: ?string, embedding?: ?list<float>} $source
     */
    private function fromSource(array $source): JobSearchDocument
    {
        return new JobSearchDocument(
            jobId: $source['jobId'],
            title: $source['title'],
            description: $source['description'],
            companyName: $source['companyName'],
            contractType: $source['contractType'],
            applyUrl: $source['applyUrl'],
            publishedAt: new \DateTimeImmutable($source['publishedAt']),
            locationLabel: $source['locationLabel'] ?? null,
            romeCode: $source['romeCode'] ?? null,
            romeLabel: $source['romeLabel'] ?? null,
            salaryDescription: $source['salaryDescription'] ?? null,
            isWorkStudy: $source['isWorkStudy'],
            skillLabels: $source['skillLabels'] ?? [],
            updatedAt: isset($source['updatedAt']) ? new \DateTimeImmutable($source['updatedAt']) : null,
            embedding: $source['embedding'] ?? null,
        );
    }

    /**
     * @param list<float> $queryEmbedding
     *
     * @return array<string, mixed>
     */
    private function searchBody(SearchQuery $query, array $queryEmbedding): array
    {
        $filters = $this->filters($query);

        return [
            'retriever' => [
                'rrf' => [
                    'retrievers' => [
                        [
                            'standard' => [
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            'multi_match' => [
                                                'query' => $query->freeText,
                                                'fields' => ['title^2', 'description', 'companyName', 'skillLabels'],
                                            ],
                                        ],
                                        'filter' => $filters,
                                    ],
                                ],
                            ],
                        ],
                        [
                            'knn' => [
                                'field' => 'embedding',
                                'query_vector' => $queryEmbedding,
                                'k' => 50,
                                'num_candidates' => 200,
                                'filter' => ['bool' => ['filter' => $filters]],
                            ],
                        ],
                    ],
                    'rank_window_size' => 50,
                    'rank_constant' => 60,
                ],
            ],
            'size' => 20,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function filters(SearchQuery $query): array
    {
        $filters = [];

        if ($query->contractType !== null) {
            $filters[] = ['term' => ['contractType' => $query->contractType]];
        }

        if ($query->romeCode !== null) {
            $filters[] = ['term' => ['romeCode' => $query->romeCode]];
        }

        return $filters;
    }

    /**
     * @param array{_score: float, _source: array{jobId: string, title: string, companyName: string, locationLabel?: ?string, applyUrl: string}} $hit
     */
    private function toResultItem(array $hit): SearchResultItem
    {
        $source = $hit['_source'];

        return new SearchResultItem(
            jobId: $source['jobId'],
            title: $source['title'],
            companyName: $source['companyName'],
            locationLabel: $source['locationLabel'] ?? null,
            applyUrl: $source['applyUrl'],
            score: $hit['_score'],
        );
    }
}
