<?php

declare(strict_types=1);

namespace Search\Infrastructure\Elasticsearch;

final class ReciprocalRankFusion
{
    private const int RANK_CONSTANT = 60;

    /**
     * Elasticsearch's own `retriever.rrf` requires a Platinum+ license, which
     * the free self-hosted Basic license doesn't have - so BM25 and kNN run
     * as two plain (license-unrestricted) searches, fused here instead.
     *
     * @param list<list<string>> $rankedIdLists each ranked best-first
     *
     * @return array<string, float> doc ID => fused score, ordered best-first
     */
    public function fuse(array $rankedIdLists): array
    {
        $scores = [];

        foreach ($rankedIdLists as $rankedIds) {
            foreach ($rankedIds as $rank => $id) {
                $scores[$id] = ($scores[$id] ?? 0.0) + 1.0 / (self::RANK_CONSTANT + $rank + 1);
            }
        }

        \arsort($scores);

        return $scores;
    }
}
