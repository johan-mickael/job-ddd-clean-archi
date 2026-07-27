<?php

declare(strict_types=1);

namespace Identity\Infrastructure\Security;

use Shared\Domain\CurrentUserIdProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class SymfonyCurrentUserIdProvider implements CurrentUserIdProviderInterface
{
    public function __construct(private readonly Security $security) {}

    public function id(): string
    {
        $user = $this->security->getUser();

        if (!$user instanceof SecurityUser) {
            throw new \RuntimeException('No authenticated user - this route must be behind the security firewall.');
        }

        return $user->userId;
    }
}
