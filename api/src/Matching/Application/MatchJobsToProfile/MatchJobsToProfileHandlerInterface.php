<?php

declare(strict_types=1);

namespace Matching\Application\MatchJobsToProfile;

interface MatchJobsToProfileHandlerInterface
{
    public function __invoke(MatchJobsToProfile $query): MatchedJobs;
}
