<?php

declare(strict_types=1);

namespace Tests\Ingestion\Infrastructure\FranceTravail;

use Ingestion\Infrastructure\FranceTravail\SearchCriteria;
use PHPUnit\Framework\TestCase;

final class SearchCriteriaTest extends TestCase
{
    public function testEmptyCriteriaProducesNoQueryParams(): void
    {
        self::assertSame([], (new SearchCriteria())->toQueryParams());
    }

    public function testKeywordsRomeCodesAndCommuneAreIncludedWhenSet(): void
    {
        $criteria = new SearchCriteria(keywords: 'php', romeCodes: ['M1805'], commune: '69383', distanceKm: 20);

        self::assertSame(
            ['motsCles' => 'php', 'codeROME' => 'M1805', 'commune' => '69383', 'distance' => '20'],
            $criteria->toQueryParams(),
        );
    }

    public function testAtMostThreeRomeCodesAreSentPerFranceTravailsOwnLimit(): void
    {
        $criteria = new SearchCriteria(romeCodes: ['M1805', 'M1806', 'M1807', 'M1808']);

        self::assertSame('M1805,M1806,M1807', $criteria->toQueryParams()['codeROME']);
    }

    public function testDistanceIsOmittedWithoutACommune(): void
    {
        $criteria = new SearchCriteria(distanceKm: 20);

        self::assertArrayNotHasKey('distance', $criteria->toQueryParams());
    }
}
