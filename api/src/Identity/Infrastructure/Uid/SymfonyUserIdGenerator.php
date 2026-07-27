<?php

declare(strict_types=1);

namespace Identity\Infrastructure\Uid;

use Identity\Domain\UserId;
use Identity\Domain\UserIdGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class SymfonyUserIdGenerator implements UserIdGeneratorInterface
{
    public function next(): UserId
    {
        return new UserId(Uuid::v7()->toRfc4122());
    }
}
