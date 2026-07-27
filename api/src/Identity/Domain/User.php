<?php

declare(strict_types=1);

namespace Identity\Domain;

final readonly class User
{
    private function __construct(
        public UserId $id,
        public Email $email,
        public string $hashedPassword,
    ) {}

    public static function register(UserId $id, Email $email, string $hashedPassword): self
    {
        return new self($id, $email, $hashedPassword);
    }

    public static function reconstitute(UserId $id, Email $email, string $hashedPassword): self
    {
        return new self($id, $email, $hashedPassword);
    }
}
