<?php

declare(strict_types=1);

namespace Catalog\Application\ListPublishedJobIds;

interface ListPublishedJobIdsHandlerInterface
{
    /**
     * @return list<string>
     */
    public function __invoke(): array;
}
