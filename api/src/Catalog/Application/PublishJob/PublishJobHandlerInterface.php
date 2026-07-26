<?php

declare(strict_types=1);

namespace Catalog\Application\PublishJob;

interface PublishJobHandlerInterface
{
    public function __invoke(PublishJob $command): void;
}
