<?php

declare(strict_types=1);

namespace CandidateProfile\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'candidate_profile')]
#[ORM\Index(name: 'candidate_profile_owner_id_idx', columns: ['owner_id'])]
final class CandidateProfileEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 36)]
    public string $ownerId;

    #[ORM\Column(type: 'string', length: 255)]
    public string $sourceFileName;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $extractedAt;

    #[ORM\Column(type: 'string', length: 20)]
    public string $seniority;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: 'json')]
    public array $domains = [];

    /**
     * @var list<string>
     */
    #[ORM\Column(type: 'json')]
    public array $roles = [];

    /**
     * @var list<array{name: string, proficiency: string}>
     */
    #[ORM\Column(type: 'json')]
    public array $languages = [];

    /**
     * @var list<array{label: string, yearsOfExperience: ?int}>
     */
    #[ORM\Column(type: 'json')]
    public array $skills = [];
}
