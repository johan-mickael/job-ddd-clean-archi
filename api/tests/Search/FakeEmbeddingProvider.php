<?php

declare(strict_types=1);

namespace Tests\Search;

use Search\Domain\EmbeddingProviderInterface;

final class FakeEmbeddingProvider implements EmbeddingProviderInterface
{
    public int $callCount = 0;

    public function embed(string $text): array
    {
        ++$this->callCount;

        return [(float) \mb_strlen($text)];
    }
}
