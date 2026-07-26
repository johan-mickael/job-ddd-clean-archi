<?php

declare(strict_types=1);

namespace Catalog\Domain;

use Catalog\Domain\Exception\InvalidApplyUrl;

final readonly class ApplyUrl
{
    public function __construct(public string $value)
    {
        $isHttpUrl = \filter_var($value, \FILTER_VALIDATE_URL) !== false
            && \str_starts_with($value, 'http');

        if (!$isHttpUrl) {
            throw InvalidApplyUrl::notAnHttpUrl($value);
        }
    }
}
