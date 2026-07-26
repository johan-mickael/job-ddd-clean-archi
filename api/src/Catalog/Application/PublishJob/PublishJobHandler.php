<?php

declare(strict_types=1);

namespace Catalog\Application\PublishJob;

use Catalog\Domain\ApplyUrl;
use Catalog\Domain\ContractType;
use Catalog\Domain\Exception\InvalidContractType;
use Catalog\Domain\Job;
use Catalog\Domain\JobId;
use Catalog\Domain\JobRepositoryInterface;

final class PublishJobHandler
{
    public function __construct(private readonly JobRepositoryInterface $jobs)
    {
    }

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
}
