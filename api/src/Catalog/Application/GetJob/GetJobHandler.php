<?php

declare(strict_types=1);

namespace Catalog\Application\GetJob;

use Catalog\Domain\Job;
use Catalog\Domain\JobId;
use Catalog\Domain\JobRepositoryInterface;
use Catalog\Domain\JobSkill;

final class GetJobHandler implements GetJobHandlerInterface
{
    public function __construct(private readonly JobRepositoryInterface $jobs) {}

    public function __invoke(GetJob $query): JobView
    {
        return $this->toView($this->jobs->ofId(new JobId($query->jobId)));
    }

    private function toView(Job $job): JobView
    {
        $details = $job->details;

        return new JobView(
            jobId: $job->id->value,
            title: $job->title,
            description: $job->description,
            companyName: $job->companyName,
            contractType: $job->contractType->value,
            applyUrl: $job->applyUrl->value,
            publishedAt: $job->publishedAt,
            status: $job->status()->value,
            companyLogoUrl: $details->companyLogoUrl,
            companyDescription: $details->companyDescription,
            locationLabel: $details->location?->label,
            locationInseeCode: $details->location?->inseeCode,
            locationPostalCode: $details->location?->postalCode,
            romeCode: $details->romeCode?->value,
            romeLabel: $details->romeLabel,
            experienceRequirement: $details->experienceRequirement?->value,
            isWorkStudy: $details->isWorkStudy,
            salaryDescription: $details->salaryDescription,
            numberOfPositions: $details->numberOfPositions,
            skills: \array_map($this->skillView(...), $details->skills),
            updatedAt: $details->updatedAt,
        );
    }

    private function skillView(JobSkill $skill): JobViewSkill
    {
        return new JobViewSkill($skill->label, $skill->romeCompetencyCode?->value, $skill->isRequired);
    }
}
