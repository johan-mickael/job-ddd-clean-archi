<?php

declare(strict_types=1);

namespace Tests\Identity\Application\RegisterUser;

use Identity\Application\RegisterUser\RegisterUser;
use Identity\Application\RegisterUser\RegisterUserHandler;
use Identity\Domain\Exception\EmailAlreadyRegistered;
use Identity\Domain\Exception\PasswordTooWeak;
use Identity\Domain\UserId;
use PHPUnit\Framework\TestCase;
use Tests\Identity\FakePasswordHasher;
use Tests\Identity\FixedUserIdGenerator;
use Tests\Identity\InMemoryUserRepository;

final class RegisterUserHandlerTest extends TestCase
{
    public function testAWellFormedRegistrationIsPersistedWithAHashedPassword(): void
    {
        $id = new UserId('0198a1b2-0000-7000-8000-000000000001');
        $users = new InMemoryUserRepository();
        $handler = new RegisterUserHandler($users, new FakePasswordHasher(), new FixedUserIdGenerator($id));

        $returnedId = $handler(new RegisterUser('jane@example.com', 'correct-horse'));

        self::assertTrue($returnedId->equals($id));
        $user = $users->ofId($id);
        self::assertSame('jane@example.com', $user->email->value);
        self::assertSame('hashed:correct-horse', $user->hashedPassword);
    }

    public function testRegisteringAnAlreadyUsedEmailIsRejected(): void
    {
        $users = new InMemoryUserRepository();
        $handler = new RegisterUserHandler($users, new FakePasswordHasher(), new FixedUserIdGenerator(new UserId('0198a1b2-0000-7000-8000-000000000001')));
        $handler(new RegisterUser('jane@example.com', 'correct-horse'));

        $this->expectException(EmailAlreadyRegistered::class);

        $handler(new RegisterUser('jane@example.com', 'another-password'));
    }

    public function testATooShortPasswordIsRejected(): void
    {
        $handler = new RegisterUserHandler(new InMemoryUserRepository(), new FakePasswordHasher(), new FixedUserIdGenerator(new UserId('0198a1b2-0000-7000-8000-000000000001')));

        $this->expectException(PasswordTooWeak::class);

        $handler(new RegisterUser('jane@example.com', 'short'));
    }
}
