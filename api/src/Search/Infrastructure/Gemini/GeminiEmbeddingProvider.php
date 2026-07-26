<?php

declare(strict_types=1);

namespace Search\Infrastructure\Gemini;

use Search\Domain\EmbeddingProviderInterface;
use Search\Domain\Exception\EmbeddingProviderUnavailable;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GeminiEmbeddingProvider implements EmbeddingProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $dimensions,
    ) {}

    public function embed(string $text): array
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:embedContent",
                [
                    'headers' => ['x-goog-api-key' => $this->apiKey],
                    'json' => [
                        'content' => ['parts' => [['text' => $text]]],
                        'outputDimensionality' => $this->dimensions,
                    ],
                ],
            );

            /** @var array{embedding?: array{values?: list<int|float>}} $data */
            $data = $response->toArray();
        } catch (\Throwable $e) {
            throw EmbeddingProviderUnavailable::dueTo($e);
        }

        if (!isset($data['embedding']['values'])) {
            throw EmbeddingProviderUnavailable::dueTo(
                new \RuntimeException('Gemini embedding response is missing embedding.values.'),
            );
        }

        return \array_map(static fn(int|float $value): float => (float) $value, $data['embedding']['values']);
    }
}
