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
     * @param list<float>        $weights       one per list, same order; unweighted (1.0 each) if omitted
     *
     * @return array<string, float> doc ID => fused score, ordered best-first
     */
    public function fuse(array $rankedIdLists, array $weights = []): array
    {
        $weights = $weights === [] ? \array_fill(0, \count($rankedIdLists), 1.0) : $weights;
        $scores = [];

        foreach ($rankedIdLists as $listIndex => $rankedIds) {
            foreach ($rankedIds as $rank => $id) {
                $scores[$id] = ($scores[$id] ?? 0.0) + $weights[$listIndex] / (self::RANK_CONSTANT + $rank + 1);
            }
        }

        \arsort($scores);

        return $scores;
    }
}
