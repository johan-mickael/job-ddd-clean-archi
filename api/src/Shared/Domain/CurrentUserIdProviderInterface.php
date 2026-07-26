<?php

declare(strict_types=1);

namespace Shared\Domain;

/**
 * Every context that needs to know "who's asking" (to stamp ownership, to
 * enforce it) depends on this instead of reaching into Identity's
 * Infrastructure directly - Identity is the only context that knows how
 * that identity is actually established (Symfony Security, a JWT, ...).
 */
interface CurrentUserIdProviderInterface
{
    public function id(): string;
}
