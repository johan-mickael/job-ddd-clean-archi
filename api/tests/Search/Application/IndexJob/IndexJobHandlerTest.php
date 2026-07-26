<?php

declare(strict_types=1);

namespace Tests\Search\Application\IndexJob;

use Catalog\Application\ExpireJob\ExpireJob;
use Catalog\Application\ExpireJob\ExpireJobHandler;
use Catalog\Application\GetJob\GetJobHandler;
use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandler;
use PHPUnit\Framework\TestCase;
use Search\Application\IndexJob\IndexJob;
use Search\Application\IndexJob\IndexJobHandler;
use Tests\Catalog\InMemoryJobRepository;
use Tests\Catalog\RecordingEventPublisher;
use Tests\Search\FakeEmbeddingProvider;
use Tests\Search\FakeJobSearchIndex;

final class IndexJobHandlerTest extends TestCase
{
    public function testItIndexesAPublishedJobWithAFreshEmbedding(): void
    {
        $jobs = new InMemoryJobRepository();
        (new PublishJobHandler($jobs, new RecordingEventPublisher()))($this->publishCommand());

        $index = new FakeJobSearchIndex();
        $embeddings = new FakeEmbeddingProvider();
        $handler = new IndexJobHandler(new GetJobHandler($jobs), $index, $embeddings);

        $handler(new IndexJob('168NPMR'));

        $document = $index->find('168NPMR');
        self::assertNotNull($document);
        self::assertSame('Développeur PHP Senior', $document->title);
        self::assertSame(1, $embeddings->callCount);
        self::assertNotNull($document->embedding);
    }

    public function testItReusesTheExistingEmbeddingWhenTheJobHasNotChanged(): void
    {
        $jobs = new InMemoryJobRepository();
        (new PublishJobHandler($jobs, new RecordingEventPublisher()))($this->publishCommand());

        $index = new FakeJobSearchIndex();
        $embeddings = new FakeEmbeddingProvider();
        $handler = new IndexJobHandler(new GetJobHandler($jobs), $index, $embeddings);

        $handler(new IndexJob('168NPMR'));
        $handler(new IndexJob('168NPMR'));

        self::assertSame(1, $embeddings->callCount);
    }

    public function testItRemovesTheJobFromTheIndexWhenItExpires(): void
    {
        $jobs = new InMemoryJobRepository();
        $events = new RecordingEventPublisher();
        (new PublishJobHandler($jobs, $events))($this->publishCommand());

        $index = new FakeJobSearchIndex();
        $handler = new IndexJobHandler(new GetJobHandler($jobs), $index, new FakeEmbeddingProvider());
        $handler(new IndexJob('168NPMR'));

        (new ExpireJobHandler($jobs, $events))(new ExpireJob('168NPMR'));
        $handler(new IndexJob('168NPMR'));

        self::assertNull($index->find('168NPMR'));
    }

    private function publishCommand(): PublishJob
    {
        return new PublishJob(
            jobId: '168NPMR',
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            publishedAt: new \DateTimeImmutable('2026-07-01'),
        );
    }
}
