<?php

declare(strict_types=1);

namespace Tests\Catalog\Application\ListPublishedJobIds;

use Catalog\Application\ExpireJob\ExpireJob;
use Catalog\Application\ExpireJob\ExpireJobHandler;
use Catalog\Application\ListPublishedJobIds\ListPublishedJobIdsHandler;
use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandler;
use PHPUnit\Framework\TestCase;
use Tests\Catalog\InMemoryJobRepository;
use Tests\Catalog\RecordingEventPublisher;

final class ListPublishedJobIdsHandlerTest extends TestCase
{
    public function testItListsOnlyPublishedJobs(): void
    {
        $jobs = new InMemoryJobRepository();
        $publishJob = new PublishJobHandler($jobs, new RecordingEventPublisher());
        $publishJob($this->publishCommand('168NPMR'));
        $publishJob($this->publishCommand('168NPMS'));
        (new ExpireJobHandler($jobs, new RecordingEventPublisher()))(new ExpireJob('168NPMS'));

        $ids = (new ListPublishedJobIdsHandler($jobs))();

        self::assertSame(['168NPMR'], $ids);
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
