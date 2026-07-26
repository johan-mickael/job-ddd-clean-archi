<?php

declare(strict_types=1);

namespace Search\Domain;

use Search\Domain\Exception\EmbeddingProviderUnavailable;

interface EmbeddingProviderInterface
{
    /**
     * @return list<float>
     *
     * @throws EmbeddingProviderUnavailable
     */
    public function embed(string $text): array;
}
