<?php

declare(strict_types=1);

namespace Search\Infrastructure\Elasticsearch;

final class JobIndexMapping
{
    /**
     * @return array<string, mixed>
     */
    public static function body(int $embeddingDimensions): array
    {
        return [
            'mappings' => [
                'properties' => [
                    'jobId' => ['type' => 'keyword'],
                    'title' => ['type' => 'text', 'analyzer' => 'french'],
                    'description' => ['type' => 'text', 'analyzer' => 'french'],
                    'companyName' => ['type' => 'text'],
                    'contractType' => ['type' => 'keyword'],
                    'applyUrl' => ['type' => 'keyword'],
                    'publishedAt' => ['type' => 'date'],
                    'locationLabel' => ['type' => 'text', 'analyzer' => 'french'],
                    'romeCode' => ['type' => 'keyword'],
                    'romeLabel' => ['type' => 'text', 'analyzer' => 'french'],
                    'salaryDescription' => ['type' => 'text'],
                    'isWorkStudy' => ['type' => 'boolean'],
                    'skillLabels' => ['type' => 'text', 'analyzer' => 'french'],
                    'updatedAt' => ['type' => 'date'],
                    'embedding' => [
                        'type' => 'dense_vector',
                        'dims' => $embeddingDimensions,
                        'index' => true,
                        'similarity' => 'cosine',
                    ],
                ],
            ],
        ];
    }
}
