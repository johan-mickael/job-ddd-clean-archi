<?php

declare(strict_types=1);

namespace Catalog\Infrastructure\Persistence\Doctrine;

use Catalog\Domain\ApplyUrl;
use Catalog\Domain\ContractType;
use Catalog\Domain\ExperienceRequirement;
use Catalog\Domain\Job;
use Catalog\Domain\JobDetails;
use Catalog\Domain\JobId;
use Catalog\Domain\JobSkill;
use Catalog\Domain\OfferStatus;
use Catalog\Domain\WorkLocation;
use Shared\Domain\RomeCode;
use Shared\Domain\RomeCompetencyCode;

final class JobMapper
{
    public function toNewEntity(Job $job): JobEntity
    {
        $entity = new JobEntity();
        $entity->id = $job->id->value;
        $this->fill($entity, $job);

        return $entity;
    }

    public function updateEntity(JobEntity $entity, Job $job): void
    {
        $this->fill($entity, $job);
    }

    public function toDomain(JobEntity $entity): Job
    {
        return Job::reconstitute(
            new JobId($entity->id),
            $entity->title,
            $entity->description,
            $entity->companyName,
            ContractType::from($entity->contractType),
            new ApplyUrl($entity->applyUrl),
            $entity->publishedAt,
            new JobDetails(
                companyLogoUrl: $entity->companyLogoUrl,
                companyDescription: $entity->companyDescription,
                location: $entity->locationLabel !== null
                    ? new WorkLocation($entity->locationLabel, $entity->locationInseeCode, $entity->locationPostalCode)
                    : null,
                romeCode: $entity->romeCode !== null ? new RomeCode($entity->romeCode) : null,
                romeLabel: $entity->romeLabel,
                experienceRequirement: $entity->experienceRequirement !== null
                    ? ExperienceRequirement::from($entity->experienceRequirement)
                    : null,
                isWorkStudy: $entity->isWorkStudy,
                salaryDescription: $entity->salaryDescription,
                numberOfPositions: $entity->numberOfPositions,
                skills: \array_map($this->skillToDomain(...), $entity->skills),
                updatedAt: $entity->updatedAt,
            ),
            OfferStatus::from($entity->status),
        );
    }

    private function fill(JobEntity $entity, Job $job): void
    {
        $entity->title = $job->title;
        $entity->description = $job->description;
        $entity->companyName = $job->companyName;
        $entity->companyLogoUrl = $job->details->companyLogoUrl;
        $entity->companyDescription = $job->details->companyDescription;
        $entity->contractType = $job->contractType->value;
        $entity->applyUrl = $job->applyUrl->value;
        $entity->status = $job->status()->value;
        $entity->locationLabel = $job->details->location?->label;
        $entity->locationInseeCode = $job->details->location?->inseeCode;
        $entity->locationPostalCode = $job->details->location?->postalCode;
        $entity->romeCode = $job->details->romeCode?->value;
        $entity->romeLabel = $job->details->romeLabel;
        $entity->experienceRequirement = $job->details->experienceRequirement?->value;
        $entity->isWorkStudy = $job->details->isWorkStudy;
        $entity->salaryDescription = $job->details->salaryDescription;
        $entity->numberOfPositions = $job->details->numberOfPositions;
        $entity->skills = \array_map($this->skillToArray(...), $job->details->skills);
        $entity->publishedAt = $job->publishedAt;
        $entity->updatedAt = $job->details->updatedAt;
    }

    /**
     * @return array{label: string, romeCompetencyCode: ?string, isRequired: bool}
     */
    private function skillToArray(JobSkill $skill): array
    {
        return [
            'label' => $skill->label,
            'romeCompetencyCode' => $skill->romeCompetencyCode?->value,
            'isRequired' => $skill->isRequired,
        ];
    }

    /**
     * @param array{label: string, romeCompetencyCode: ?string, isRequired: bool} $skill
     */
    private function skillToDomain(array $skill): JobSkill
    {
        return new JobSkill(
            $skill['label'],
            $skill['romeCompetencyCode'] !== null ? new RomeCompetencyCode($skill['romeCompetencyCode']) : null,
            $skill['isRequired'],
        );
    }
}
