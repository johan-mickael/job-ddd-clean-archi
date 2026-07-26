<?php

declare(strict_types=1);

namespace Tests\Identity;

use Identity\Domain\UserId;
use Identity\Domain\UserIdGeneratorInterface;

final class FixedUserIdGenerator implements UserIdGeneratorInterface
{
    public function __construct(private readonly UserId $id) {}

    public function next(): UserId
    {
        return $this->id;
    }
}
