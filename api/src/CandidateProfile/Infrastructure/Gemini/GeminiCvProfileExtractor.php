<?php

declare(strict_types=1);

namespace CandidateProfile\Infrastructure\Gemini;

use CandidateProfile\Domain\CvProfileExtractorInterface;
use CandidateProfile\Domain\Exception\CvProfileExtractionFailed;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GeminiCvProfileExtractor implements CvProfileExtractorInterface
{
    private const string PROMPT = <<<'PROMPT'
        You extract a structured profile from a CV/resume. Read the CV text
        below and return only what the CV actually supports - never invent
        skills, roles, or experience it doesn't mention.

        CV text:
        %s
        PROMPT;

    /**
     * @var array<string, mixed>
     */
    private const array RESPONSE_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'seniority' => [
                'type' => 'string',
                'enum' => ['junior', 'mid', 'senior', 'lead', 'principal'],
            ],
            'domains' => ['type' => 'array', 'items' => ['type' => 'string']],
            'roles' => ['type' => 'array', 'items' => ['type' => 'string']],
            'languages' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'proficiency' => [
                            'type' => 'string',
                            'enum' => ['basic', 'conversational', 'fluent', 'native'],
                        ],
                    ],
                    'required' => ['name', 'proficiency'],
                ],
            ],
            'skills' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'label' => ['type' => 'string'],
                        'yearsOfExperience' => ['type' => 'integer', 'nullable' => true],
                    ],
                    'required' => ['label'],
                ],
            ],
        ],
        'required' => ['seniority', 'domains', 'roles', 'languages', 'skills'],
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function extract(string $cvText): array
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent",
                [
                    'headers' => ['x-goog-api-key' => $this->apiKey],
                    'json' => [
                        'contents' => [['parts' => [['text' => \sprintf(self::PROMPT, $cvText)]]]],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json',
                            'responseSchema' => self::RESPONSE_SCHEMA,
                        ],
                    ],
                ],
            );

            /** @var array{candidates?: list<array{content?: array{parts?: list<array{text?: string}>}}>} $data */
            $data = $response->toArray();
        } catch (\Throwable $e) {
            throw CvProfileExtractionFailed::dueTo($e);
        }

        $json = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!\is_string($json)) {
            throw CvProfileExtractionFailed::dueTo(new \RuntimeException('Gemini response is missing candidates[0].content.parts[0].text.'));
        }

        $decoded = \json_decode($json, true);

        if (!\is_array($decoded)) {
            throw CvProfileExtractionFailed::dueTo(new \RuntimeException('Gemini response text is not valid JSON.'));
        }

        /** @var array<string, mixed> */
        return $decoded;
    }
}
