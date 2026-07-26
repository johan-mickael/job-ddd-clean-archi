<?php

declare(strict_types=1);

namespace Identity\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'identity_user')]
#[ORM\UniqueConstraint(name: 'identity_user_email_unique', columns: ['email'])]
final class UserEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $email;

    #[ORM\Column(type: 'string', length: 255)]
    public string $hashedPassword;
}
