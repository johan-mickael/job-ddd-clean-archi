<?php

declare(strict_types=1);

namespace Identity\Application\RegisterUser;

use Identity\Domain\Email;
use Identity\Domain\Exception\EmailAlreadyRegistered;
use Identity\Domain\Exception\PasswordTooWeak;
use Identity\Domain\PasswordHasherInterface;
use Identity\Domain\User;
use Identity\Domain\UserId;
use Identity\Domain\UserIdGeneratorInterface;
use Identity\Domain\UserRepositoryInterface;

final class RegisterUserHandler implements RegisterUserHandlerInterface
{
    private const int MIN_PASSWORD_LENGTH = 8;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly PasswordHasherInterface $passwordHasher,
        private readonly UserIdGeneratorInterface $idGenerator,
    ) {}

    public function __invoke(RegisterUser $command): UserId
    {
        $email = new Email($command->email);

        if ($this->users->existsWithEmail($email)) {
            throw EmailAlreadyRegistered::withEmail($email);
        }

        if (\mb_strlen($command->plainPassword) < self::MIN_PASSWORD_LENGTH) {
            throw PasswordTooWeak::tooShort(self::MIN_PASSWORD_LENGTH);
        }

        $id = $this->idGenerator->next();
        $user = User::register($id, $email, $this->passwordHasher->hash($command->plainPassword));

        $this->users->add($user);

        return $id;
    }
}
