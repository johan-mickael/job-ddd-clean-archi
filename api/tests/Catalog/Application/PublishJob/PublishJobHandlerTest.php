<?php

declare(strict_types=1);

namespace Tests\Catalog\Application\PublishJob;

use Catalog\Domain\Exception\InvalidContractType;
use Catalog\Domain\JobId;
use Catalog\Application\PublishJob\PublishJob;
use Catalog\Application\PublishJob\PublishJobHandler;
use PHPUnit\Framework\TestCase;

final class PublishJobHandlerTest extends TestCase
{
    public function testItPublishesAndPersistsTheJob(): void
    {
        $jobs = new InMemoryJobRepository();
        $handler = new PublishJobHandler($jobs);

        ($handler)($this->command());

        self::assertSame('published', $jobs->ofId(new JobId('168NPMR'))->status()->value);
    }

    public function testItRejectsAnUnrecognizedContractType(): void
    {
        $handler = new PublishJobHandler(new InMemoryJobRepository());

        $this->expectException(InvalidContractType::class);

        ($handler)(new PublishJob(
            jobId: '168NPMR',
            title: 'Développeur PHP Senior',
            description: 'Vous rejoignez une équipe backend...',
            companyName: 'Acme Corp',
            contractType: 'not-a-real-code',
            applyUrl: 'https://candidat.francetravail.fr/offres/recherche/detail/168NPMR',
            publishedAt: new \DateTimeImmutable('2026-07-01'),
        ));
    }

    private function command(): PublishJob
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
