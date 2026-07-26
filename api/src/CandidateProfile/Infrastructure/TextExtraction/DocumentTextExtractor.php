<?php

declare(strict_types=1);

namespace CandidateProfile\Infrastructure\TextExtraction;

use CandidateProfile\Domain\Exception\CvTextNotExtractable;
use CandidateProfile\Domain\TextExtractorInterface;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

final class DocumentTextExtractor implements TextExtractorInterface
{
    public function extract(string $fileContent, string $fileName): string
    {
        $extension = \mb_strtolower(\pathinfo($fileName, \PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => $this->extractPdf($fileContent, $fileName),
            'docx' => $this->extractWord($fileContent, $fileName, 'Word2007'),
            'doc' => $this->extractWord($fileContent, $fileName, 'MsDoc'),
            default => throw CvTextNotExtractable::unsupportedFileType($fileName),
        };
    }

    private function extractPdf(string $fileContent, string $fileName): string
    {
        try {
            return (new PdfParser())->parseContent($fileContent)->getText();
        } catch (\Throwable $e) {
            throw CvTextNotExtractable::dueTo($fileName, $e);
        }
    }

    private function extractWord(string $fileContent, string $fileName, string $readerName): string
    {
        $tmpPath = \tempnam(\sys_get_temp_dir(), 'cv');

        if ($tmpPath === false) {
            throw CvTextNotExtractable::dueTo($fileName, new \RuntimeException('Could not create a temporary file.'));
        }

        try {
            \file_put_contents($tmpPath, $fileContent);
            $document = IOFactory::load($tmpPath, $readerName);

            $lines = [];
            foreach ($document->getSections() as $section) {
                $lines[] = $this->containerToText($section);
            }

            return \implode("\n", \array_filter($lines, static fn(string $line): bool => $line !== ''));
        } catch (\Throwable $e) {
            throw CvTextNotExtractable::dueTo($fileName, $e);
        } finally {
            \unlink($tmpPath);
        }
    }

    private function containerToText(AbstractContainer $container): string
    {
        $lines = \array_map($this->elementToText(...), $container->getElements());

        return \implode("\n", \array_filter($lines, static fn(string $line): bool => $line !== ''));
    }

    private function elementToText(mixed $element): string
    {
        return match (true) {
            $element instanceof Text => $element->getText(),
            $element instanceof TextRun => $this->containerToText($element),
            $element instanceof Table => \implode("\n", \array_map(
                fn($row): string => \implode(' | ', \array_map($this->containerToText(...), $row->getCells())),
                $element->getRows(),
            )),
            default => '',
        };
    }
}
