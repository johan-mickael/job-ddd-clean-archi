<?php

declare(strict_types=1);

namespace Tests\Search\Application\ReindexAllJobs;

use Catalog\Application\GetJob\GetJobHandler;
use Catalog\Application\ListPublishedJobIds\ListPublishedJobIdsHandler;
use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandler;
use PHPUnit\Framework\TestCase;
use Search\Application\IndexJob\IndexJobHandler;
use Search\Application\ReindexAllJobs\ReindexAllJobs;
use Search\Application\ReindexAllJobs\ReindexAllJobsHandler;
use Tests\Catalog\InMemoryJobRepository;
use Tests\Catalog\RecordingEventPublisher;
use Tests\Search\FakeEmbeddingProvider;
use Tests\Search\FakeJobSearchIndex;

final class ReindexAllJobsHandlerTest extends TestCase
{
    public function testItReindexesEveryPublishedJob(): void
    {
        $jobs = new InMemoryJobRepository();
        $events = new RecordingEventPublisher();
        $publishJob = new PublishJobHandler($jobs, $events);
        $publishJob($this->publishCommand('168NPMR'));
        $publishJob($this->publishCommand('168NPMS'));

        $index = new FakeJobSearchIndex();
        $indexJob = new IndexJobHandler(new GetJobHandler($jobs), $index, new FakeEmbeddingProvider());
        $handler = new ReindexAllJobsHandler(new ListPublishedJobIdsHandler($jobs), $indexJob);

        $count = $handler(new ReindexAllJobs());

        self::assertSame(2, $count);
        self::assertNotNull($index->find('168NPMR'));
        self::assertNotNull($index->find('168NPMS'));
    }

    private function publishCommand(string $jobId): PublishJob
    {
        return new PublishJob(
            jobId: $jobId,
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'cdi',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/' . $jobId,
            publishedAt: new \DateTimeImmutable('2026-07-01'),
        );
    }
}
