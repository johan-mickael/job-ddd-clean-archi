<?php

declare(strict_types=1);

namespace Catalog\Application\PublishJob;

use Catalog\Domain\ApplyUrl;
use Catalog\Domain\ContractType;
use Catalog\Domain\Exception\InvalidContractType;
use Catalog\Domain\ExperienceRequirement;
use Catalog\Domain\Job;
use Catalog\Domain\JobDetails;
use Catalog\Domain\JobId;
use Catalog\Domain\JobRepositoryInterface;
use Catalog\Domain\JobSkill;
use Catalog\Domain\WorkLocation;
use Shared\Domain\RomeCode;
use Shared\Domain\RomeCompetencyCode;

final class PublishJobHandler implements PublishJobHandlerInterface
{
    public function __construct(private readonly JobRepositoryInterface $jobs) {}

    public function __invoke(PublishJob $command): void
    {
        $job = Job::publish(
            new JobId($command->jobId),
            $command->title,
            $command->description,
            $command->companyName,
            $this->contractType($command->contractType),
            new ApplyUrl($command->applyUrl),
            $command->publishedAt,
            $this->details($command),
        );

        $this->jobs->add($job);
    }

    private function contractType(string $code): ContractType
    {
        try {
            return ContractType::from($code);
        } catch (\ValueError) {
            throw InvalidContractType::unrecognized($code);
        }
    }

    private function details(PublishJob $command): JobDetails
    {
        return new JobDetails(
            companyLogoUrl: $command->companyLogoUrl,
            companyDescription: $command->companyDescription,
            location: $this->location($command),
            romeCode: $command->romeCode !== null ? new RomeCode($command->romeCode) : null,
            romeLabel: $command->romeLabel,
            experienceRequirement: $command->experienceRequirement !== null
                ? ExperienceRequirement::tryFrom($command->experienceRequirement)
                : null,
            isWorkStudy: $command->isWorkStudy,
            salaryDescription: $command->salaryDescription,
            numberOfPositions: $command->numberOfPositions ?? 1,
            skills: \array_map($this->skill(...), $command->skills),
            updatedAt: $command->updatedAt,
        );
    }

    private function location(PublishJob $command): ?WorkLocation
    {
        if ($command->locationLabel === null) {
            return null;
        }

        return new WorkLocation($command->locationLabel, $command->locationInseeCode, $command->locationPostalCode);
    }

    private function skill(PublishJobSkill $skill): JobSkill
    {
        return new JobSkill(
            $skill->label,
            $skill->romeCompetencyCode !== null ? new RomeCompetencyCode($skill->romeCompetencyCode) : null,
            $skill->isRequired,
        );
    }
}
