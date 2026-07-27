<?php

declare(strict_types=1);

namespace Identity\Application\RegisterUser;

use Identity\Domain\UserId;

interface RegisterUserHandlerInterface
{
    public function __invoke(RegisterUser $command): UserId;
}
