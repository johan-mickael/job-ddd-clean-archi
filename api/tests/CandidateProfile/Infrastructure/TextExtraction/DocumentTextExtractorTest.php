<?php

declare(strict_types=1);

namespace Tests\CandidateProfile\Infrastructure\TextExtraction;

use CandidateProfile\Domain\Exception\CvTextNotExtractable;
use CandidateProfile\Infrastructure\TextExtraction\DocumentTextExtractor;
use PHPUnit\Framework\TestCase;

final class DocumentTextExtractorTest extends TestCase
{
    public function testAnUnsupportedFileTypeIsRejected(): void
    {
        $this->expectException(CvTextNotExtractable::class);

        (new DocumentTextExtractor())->extract('irrelevant content', 'cv.txt');
    }

    public function testAFileWithNoExtensionIsRejected(): void
    {
        $this->expectException(CvTextNotExtractable::class);

        (new DocumentTextExtractor())->extract('irrelevant content', 'cv');
    }
}
