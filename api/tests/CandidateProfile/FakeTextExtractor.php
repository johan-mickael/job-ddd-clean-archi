<?php

declare(strict_types=1);

namespace Tests\CandidateProfile;

use CandidateProfile\Domain\TextExtractorInterface;

final class FakeTextExtractor implements TextExtractorInterface
{
    public function __construct(private readonly string $text = 'Senior PHP developer, 5 years of experience.') {}

    public function extract(string $fileContent, string $fileName): string
    {
        return $this->text;
    }
}
