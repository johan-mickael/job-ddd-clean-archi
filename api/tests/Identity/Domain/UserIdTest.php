<?php

declare(strict_types=1);

namespace Tests\Identity\Domain;

use Identity\Domain\Exception\InvalidUserId;
use Identity\Domain\UserId;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    public function testABlankValueIsRejected(): void
    {
        $this->expectException(InvalidUserId::class);

        new UserId('   ');
    }

    public function testTwoIdsWithTheSameValueAreEqual(): void
    {
        self::assertTrue((new UserId('0198a1b2-0000-7000-8000-000000000001'))->equals(new UserId('0198a1b2-0000-7000-8000-000000000001')));
    }

    public function testTwoIdsWithDifferentValuesAreNotEqual(): void
    {
        self::assertFalse((new UserId('0198a1b2-0000-7000-8000-000000000001'))->equals(new UserId('0198a1b2-0000-7000-8000-000000000002')));
    }
}
