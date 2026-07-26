<?php

declare(strict_types=1);

namespace Tests\Search\Application\SearchJobs;

use PHPUnit\Framework\TestCase;
use Search\Application\SearchJobs\SearchJobs;
use Search\Application\SearchJobs\SearchJobsHandler;
use Search\Domain\JobSearchDocument;
use Tests\Search\FakeEmbeddingProvider;
use Tests\Search\FakeJobSearchIndex;

final class SearchJobsHandlerTest extends TestCase
{
    public function testItReturnsResultsFromTheIndex(): void
    {
        $index = new FakeJobSearchIndex();
        $index->index(new JobSearchDocument(
            jobId: '168NPMR',
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            publishedAt: new \DateTimeImmutable('2026-07-01'),
            locationLabel: 'Lyon - 69003',
            romeCode: 'M1805',
            romeLabel: null,
            salaryDescription: null,
            isWorkStudy: false,
            skillLabels: ['PHP'],
            updatedAt: null,
            embedding: [0.1, 0.2],
        ));

        $handler = new SearchJobsHandler($index, new FakeEmbeddingProvider());

        $results = $handler(new SearchJobs('développeur PHP'));

        self::assertCount(1, $results->items);
        self::assertSame('168NPMR', $results->items[0]->jobId);
    }

    public function testItEmbedsTheFreeTextBeforeSearching(): void
    {
        $embeddings = new FakeEmbeddingProvider();
        $handler = new SearchJobsHandler(new FakeJobSearchIndex(), $embeddings);

        $handler(new SearchJobs('développeur PHP'));

        self::assertSame(1, $embeddings->callCount);
    }
}
