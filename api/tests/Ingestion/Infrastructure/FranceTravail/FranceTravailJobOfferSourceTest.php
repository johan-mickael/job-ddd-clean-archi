<?php

declare(strict_types=1);

namespace Tests\Ingestion\Infrastructure\FranceTravail;

use Ingestion\Domain\Exception\JobOfferSourceUnavailable;
use Ingestion\Infrastructure\FranceTravail\FranceTravailJobOfferSource;
use Ingestion\Infrastructure\FranceTravail\JobOfferPayloadTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class FranceTravailJobOfferSourceTest extends TestCase
{
    public function testItYieldsTranslatedOffersFromASinglePage(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(\json_encode(['resultats' => [
                $this->rawOffer('168NPMR'),
                $this->rawOffer('168NPMS'),
            ]]), ['response_headers' => ['Content-Range' => 'offres 0-1/2']]),
        ]);

        $offers = \iterator_to_array($this->source($httpClient)->offersPublishedSince(new \DateTimeImmutable('-1 day')));

        self::assertCount(2, $offers);
        self::assertSame('168NPMR', $offers[0]->offerId);
        self::assertSame('168NPMS', $offers[1]->offerId);
        self::assertSame(1, $httpClient->getRequestsCount());
    }

    public function testItPaginatesUntilAllResultsAreFetched(): void
    {
        $firstPage = \array_map(fn (int $i) => $this->rawOffer('OFFER'.$i), \range(0, 149));

        $httpClient = new MockHttpClient([
            new MockResponse(\json_encode(['resultats' => $firstPage]), ['response_headers' => ['Content-Range' => 'offres 0-149/151']]),
            new MockResponse(\json_encode(['resultats' => [$this->rawOffer('OFFER150')]]), ['response_headers' => ['Content-Range' => 'offres 150-150/151']]),
        ]);

        $offers = \iterator_to_array($this->source($httpClient)->offersPublishedSince(new \DateTimeImmutable('-1 day')));

        self::assertCount(151, $offers);
        self::assertSame(2, $httpClient->getRequestsCount());
    }

    public function testItSendsTheBearerTokenAndTheCreationDateRange(): void
    {
        $sentOptions = null;
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$sentOptions): MockResponse {
            $sentOptions = $options;

            return new MockResponse(\json_encode(['resultats' => []]), ['response_headers' => ['Content-Range' => 'offres 0-0/0']]);
        });

        \iterator_to_array($this->source($httpClient)->offersPublishedSince(new \DateTimeImmutable('2026-07-01T00:00:00Z')));

        self::assertNotNull($sentOptions);
        self::assertContains('Authorization: Bearer fixed-token', $sentOptions['headers']);
        self::assertSame('0-149', $sentOptions['query']['range']);
        self::assertSame('2026-07-01T00:00:00Z', $sentOptions['query']['minCreationDate']);
    }

    public function testItWrapsHttpFailuresAsJobOfferSourceUnavailable(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"message":"server error"}', ['http_code' => 500]),
        ]);

        $this->expectException(JobOfferSourceUnavailable::class);

        \iterator_to_array($this->source($httpClient)->offersPublishedSince(new \DateTimeImmutable('-1 day')));
    }

    private function source(MockHttpClient $httpClient): FranceTravailJobOfferSource
    {
        return new FranceTravailJobOfferSource(
            $httpClient,
            new FixedAccessTokenProvider(),
            new JobOfferPayloadTranslator(),
            'https://api.francetravail.io/partenaire/offresdemploi/v2',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function rawOffer(string $id): array
    {
        return [
            'id' => $id,
            'intitule' => 'Développeur PHP Senior',
            'description' => 'Vous rejoignez une équipe backend...',
            'typeContrat' => 'CDI',
            'dateCreation' => '2026-07-01T08:00:00.000Z',
            'entreprise' => ['nom' => 'Acme Corp'],
            'origineOffre' => ['urlOrigine' => 'https://candidat.francetravail.fr/offres/recherche/detail/'.$id],
        ];
    }
}
