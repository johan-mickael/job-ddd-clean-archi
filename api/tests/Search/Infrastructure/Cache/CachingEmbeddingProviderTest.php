<?php

declare(strict_types=1);

namespace Tests\Search\Infrastructure\Cache;

use PHPUnit\Framework\TestCase;
use Search\Domain\EmbeddingProviderInterface;
use Search\Infrastructure\Cache\CachingEmbeddingProvider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachingEmbeddingProviderTest extends TestCase
{
    public function testTheSameTextIsOnlyEmbeddedOnce(): void
    {
        $inner = new class implements EmbeddingProviderInterface {
            public int $calls = 0;

            public function embed(string $text): array
            {
                ++$this->calls;

                return [0.1, 0.2];
            }
        };

        $provider = new CachingEmbeddingProvider($inner, new ArrayAdapter());

        $first = $provider->embed('remote PHP jobs');
        $second = $provider->embed('remote PHP jobs');

        self::assertSame([0.1, 0.2], $first);
        self::assertSame($first, $second);
        self::assertSame(1, $inner->calls);
    }

    public function testDifferentTextIsEmbeddedSeparately(): void
    {
        $inner = new class implements EmbeddingProviderInterface {
            /** @var list<string> */
            public array $seen = [];

            public function embed(string $text): array
            {
                $this->seen[] = $text;

                return [0.0];
            }
        };

        $provider = new CachingEmbeddingProvider($inner, new ArrayAdapter());

        $provider->embed('php');
        $provider->embed('java');

        self::assertSame(['php', 'java'], $inner->seen);
    }

    public function testTextIsNormalizedBeforeHashing(): void
    {
        $inner = new class implements EmbeddingProviderInterface {
            public int $calls = 0;

            public function embed(string $text): array
            {
                ++$this->calls;

                return [0.0];
            }
        };

        $provider = new CachingEmbeddingProvider($inner, new ArrayAdapter());

        $provider->embed('  PHP Jobs  ');
        $provider->embed('php jobs');

        self::assertSame(1, $inner->calls);
    }
}
