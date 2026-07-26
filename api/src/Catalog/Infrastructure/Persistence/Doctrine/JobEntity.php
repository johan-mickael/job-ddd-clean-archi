<?php

declare(strict_types=1);

namespace Catalog\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'catalog_job')]
final class JobEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 20)]
    public string $id;

    #[ORM\Column(type: 'string', length: 300)]
    public string $title;

    #[ORM\Column(type: 'text')]
    public string $description;

    #[ORM\Column(type: 'string', length: 255)]
    public string $companyName;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    public ?string $companyLogoUrl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $companyDescription = null;

    #[ORM\Column(type: 'string', length: 20)]
    public string $contractType;

    #[ORM\Column(type: 'string', length: 500)]
    public string $applyUrl;

    #[ORM\Column(type: 'string', length: 20)]
    public string $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    public ?string $locationLabel = null;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    public ?string $locationInseeCode = null;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    public ?string $locationPostalCode = null;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    public ?string $romeCode = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    public ?string $romeLabel = null;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    public ?string $experienceRequirement = null;

    #[ORM\Column(type: 'boolean')]
    public bool $isWorkStudy;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $salaryDescription = null;

    #[ORM\Column(type: 'integer')]
    public int $numberOfPositions;

    /**
     * @var list<array{label: string, romeCompetencyCode: ?string, isRequired: bool}>
     */
    #[ORM\Column(type: 'json')]
    public array $skills = [];

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $publishedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;
}
