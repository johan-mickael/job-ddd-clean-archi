<?php

declare(strict_types=1);

namespace Matching\Application\MatchJobsToProfile;

final readonly class MatchedJobs
{
    /**
     * @param list<MatchedJobView> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $pageSize,
        public int $total,
        public int $totalPages,
    ) {}
}
