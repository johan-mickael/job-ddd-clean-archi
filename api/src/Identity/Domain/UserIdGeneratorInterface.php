<?php

declare(strict_types=1);

namespace Identity\Domain;

interface UserIdGeneratorInterface
{
    public function next(): UserId;
}
