<?php

declare(strict_types=1);

namespace Tests\Search\Infrastructure\Elasticsearch;

use PHPUnit\Framework\TestCase;
use Search\Infrastructure\Elasticsearch\ReciprocalRankFusion;

final class ReciprocalRankFusionTest extends TestCase
{
    public function testADocumentRankedInBothListsOutscoresOneRankedInOnly(): void
    {
        $fused = (new ReciprocalRankFusion())->fuse([
            ['a', 'b'],
            ['b', 'c'],
        ]);

        self::assertSame(['b', 'a', 'c'], \array_keys($fused));
    }

    public function testATopRankInOneListCanOutscoreAMidRankInBoth(): void
    {
        $fused = (new ReciprocalRankFusion())->fuse([
            ['a'],
            [],
        ]);

        self::assertSame(['a'], \array_keys($fused));
        self::assertGreaterThan(0.0, $fused['a']);
    }

    public function testEmptyListsProduceNoResults(): void
    {
        self::assertSame([], (new ReciprocalRankFusion())->fuse([[], []]));
    }

    public function testAHeavilyWeightedListCanOutrankATopRankInAnUnweightedOne(): void
    {
        $fused = (new ReciprocalRankFusion())->fuse(
            [
                ['b'],
                ['a'],
            ],
            [0.7, 0.3],
        );

        self::assertSame(['b', 'a'], \array_keys($fused));
    }
}
