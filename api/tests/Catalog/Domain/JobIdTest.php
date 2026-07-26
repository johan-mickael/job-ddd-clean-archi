<?php

declare(strict_types=1);

namespace Tests\Catalog\Domain;

use Catalog\Domain\Exception\InvalidJobId;
use Catalog\Domain\JobId;
use PHPUnit\Framework\TestCase;

final class JobIdTest extends TestCase
{
    public function testBlankValueIsRejected(): void
    {
        $this->expectException(InvalidJobId::class);

        new JobId('   ');
    }

    public function testTwoJobIdsWithTheSameValueAreEqual(): void
    {
        self::assertTrue((new JobId('168NPMR'))->equals(new JobId('168NPMR')));
    }

    public function testTwoJobIdsWithDifferentValuesAreNotEqual(): void
    {
        self::assertFalse((new JobId('168NPMR'))->equals(new JobId('999ZZZZ')));
    }
}
