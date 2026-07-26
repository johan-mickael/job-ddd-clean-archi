<?php

declare(strict_types=1);

namespace Tests\Catalog\Domain;

use Catalog\Domain\ApplyUrl;
use Catalog\Domain\ContractType;
use Catalog\Domain\Exception\OfferAlreadyExpired;
use Catalog\Domain\Job;
use Catalog\Domain\JobId;
use Catalog\Domain\OfferStatus;
use PHPUnit\Framework\TestCase;

final class JobTest extends TestCase
{
    public function testPublishingAJobMakesItPublished(): void
    {
        $job = $this->aPublishedJob();

        self::assertSame(OfferStatus::Published, $job->status());
    }

    public function testExpiringAPublishedJobMarksItExpired(): void
    {
        $job = $this->aPublishedJob();

        $job->expire();

        self::assertTrue($job->isExpired());
    }

    public function testExpiringAnAlreadyExpiredJobThrows(): void
    {
        $job = $this->aPublishedJob();
        $job->expire();

        $this->expectException(OfferAlreadyExpired::class);

        $job->expire();
    }

    private function aPublishedJob(): Job
    {
        return Job::publish(
            new JobId('168NPMR'),
            'Développeur PHP Senior',
            'Vous rejoignez une équipe backend...',
            'Acme Corp',
            ContractType::PermanentContract,
            new ApplyUrl('https://candidat.francetravail.fr/offres/recherche/detail/168NPMR'),
            new \DateTimeImmutable('2026-07-01'),
        );
    }
}
