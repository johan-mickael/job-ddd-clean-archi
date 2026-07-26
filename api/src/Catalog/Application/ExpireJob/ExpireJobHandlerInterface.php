<?php

declare(strict_types=1);

namespace Catalog\Application\ExpireJob;

interface ExpireJobHandlerInterface
{
    public function __invoke(ExpireJob $command): void;
}
