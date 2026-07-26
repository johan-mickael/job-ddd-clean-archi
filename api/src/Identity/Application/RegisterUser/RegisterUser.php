<?php

declare(strict_types=1);

namespace Identity\Application\RegisterUser;

final readonly class RegisterUser
{
    public function __construct(
        public string $email,
        public string $plainPassword,
    ) {}
}
