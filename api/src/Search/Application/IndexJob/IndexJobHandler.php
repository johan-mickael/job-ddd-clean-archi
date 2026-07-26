<?php

declare(strict_types=1);

namespace Search\Application\IndexJob;

use Catalog\Application\GetJob\GetJob;
use Catalog\Application\GetJob\GetJobHandlerInterface;
use Catalog\Application\GetJob\JobView;
use Catalog\Application\GetJob\JobViewSkill;
use Search\Domain\EmbeddingProviderInterface;
use Search\Domain\JobSearchDocument;
use Search\Domain\JobSearchIndexInterface;

final class IndexJobHandler
{
    public function __construct(
        private readonly GetJobHandlerInterface $getJob,
        private readonly JobSearchIndexInterface $index,
        private readonly EmbeddingProviderInterface $embeddings,
    ) {}

    public function __invoke(IndexJob $command): void
    {
        $view = ($this->getJob)(new GetJob($command->jobId));

        if ($view->status !== 'published') {
            $this->index->delete($command->jobId);

            return;
        }

        $existing = $this->index->find($command->jobId);

        $this->index->index($this->toDocument($view, $this->embedding($view, $existing)));
    }

    /**
     * @return list<float>
     */
    private function embedding(JobView $view, ?JobSearchDocument $existing): array
    {
        if ($existing !== null && $existing->updatedAt === $view->updatedAt && $existing->embedding !== null) {
            return $existing->embedding;
        }

        return $this->embeddings->embed($view->title . "\n" . $view->description);
    }

    /**
     * @param list<float> $embedding
     */
    private function toDocument(JobView $view, array $embedding): JobSearchDocument
    {
        return new JobSearchDocument(
            jobId: $view->jobId,
            title: $view->title,
            description: $view->description,
            companyName: $view->companyName,
            contractType: $view->contractType,
            applyUrl: $view->applyUrl,
            publishedAt: $view->publishedAt,
            locationLabel: $view->locationLabel,
            romeCode: $view->romeCode,
            romeLabel: $view->romeLabel,
            salaryDescription: $view->salaryDescription,
            isWorkStudy: $view->isWorkStudy,
            skillLabels: \array_map(static fn(JobViewSkill $skill): string => $skill->label, $view->skills),
            updatedAt: $view->updatedAt,
            embedding: $embedding,
        );
    }
}
