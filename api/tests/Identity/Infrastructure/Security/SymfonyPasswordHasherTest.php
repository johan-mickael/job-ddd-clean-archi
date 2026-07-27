<?php

declare(strict_types=1);

namespace Tests\Identity\Infrastructure\Security;

use Identity\Domain\PasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SymfonyPasswordHasherTest extends KernelTestCase
{
    private PasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        self::bootKernel();
        $hasher = self::getContainer()->get(PasswordHasherInterface::class);
        \assert($hasher instanceof PasswordHasherInterface);
        $this->hasher = $hasher;
    }

    public function testAHashedPasswordVerifiesAgainstItsOwnPlainPassword(): void
    {
        $hashed = $this->hasher->hash('correct-horse-battery-staple');

        self::assertTrue($this->hasher->verify($hashed, 'correct-horse-battery-staple'));
    }

    public function testAHashedPasswordDoesNotVerifyAgainstAWrongPassword(): void
    {
        $hashed = $this->hasher->hash('correct-horse-battery-staple');

        self::assertFalse($this->hasher->verify($hashed, 'wrong-password'));
    }

    public function testHashingIsNotPlaintext(): void
    {
        self::assertNotSame('correct-horse-battery-staple', $this->hasher->hash('correct-horse-battery-staple'));
    }
}
