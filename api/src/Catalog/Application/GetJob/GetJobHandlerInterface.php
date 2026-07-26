<?php

declare(strict_types=1);

namespace Catalog\Application\GetJob;

use Catalog\Domain\Exception\JobNotFound;

interface GetJobHandlerInterface
{
    /**
     * @throws JobNotFound
     */
    public function __invoke(GetJob $query): JobView;
}
