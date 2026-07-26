<?php

declare(strict_types=1);

namespace Tests\Identity\Domain;

use Identity\Domain\Email;
use Identity\Domain\Exception\InvalidEmail;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testItNormalizesCaseAndSurroundingWhitespace(): void
    {
        $email = new Email('  Jane.Doe@Example.COM  ');

        self::assertSame('jane.doe@example.com', $email->value);
    }

    public function testABlankValueIsRejected(): void
    {
        $this->expectException(InvalidEmail::class);

        new Email('   ');
    }

    public function testAMalformedValueIsRejected(): void
    {
        $this->expectException(InvalidEmail::class);

        new Email('not-an-email');
    }

    public function testTwoEmailsWithDifferentCasingAreEqual(): void
    {
        self::assertTrue((new Email('Jane@Example.com'))->equals(new Email('jane@example.com')));
    }
}
