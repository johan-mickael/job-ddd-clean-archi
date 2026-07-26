<?php

declare(strict_types=1);

namespace Search\Infrastructure\Cache;

use Psr\Cache\InvalidArgumentException;
use Search\Domain\EmbeddingProviderInterface;
use Search\Domain\Exception\EmbeddingProviderUnavailable;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachingEmbeddingProvider implements EmbeddingProviderInterface
{
    private const int TTL_SECONDS = 3600;

    public function __construct(
        private readonly EmbeddingProviderInterface $inner,
        private readonly CacheInterface $cache,
    ) {}

    public function embed(string $text): array
    {
        $key = 'search.embedding.' . \hash('xxh128', \mb_strtolower(\trim($text)));

        try {
            /** @var list<float> */
            return $this->cache->get($key, function (ItemInterface $item) use ($text): array {
                $item->expiresAfter(self::TTL_SECONDS);

                return $this->inner->embed($text);
            });
        } catch (InvalidArgumentException $e) {
            throw EmbeddingProviderUnavailable::dueTo($e);
        }
    }
}
