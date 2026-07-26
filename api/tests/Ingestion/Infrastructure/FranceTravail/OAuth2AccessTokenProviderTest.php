<?php

declare(strict_types=1);

namespace Tests\Ingestion\Infrastructure\FranceTravail;

use Ingestion\Infrastructure\FranceTravail\Exception\AccessTokenRequestFailed;
use Ingestion\Infrastructure\FranceTravail\OAuth2AccessTokenProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class OAuth2AccessTokenProviderTest extends TestCase
{
    public function testItReturnsTheTokenFromAWellFormedResponse(): void
    {
        $provider = $this->provider(new MockHttpClient([
            new MockResponse(\json_encode(['access_token' => 'abc123', 'expires_in' => 1500])),
        ]));

        self::assertSame('abc123', $provider->provideToken()->value);
    }

    public function testItReusesTheCachedTokenInsteadOfRequestingATwice(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(\json_encode(['access_token' => 'abc123', 'expires_in' => 1500])),
        ]);
        $provider = $this->provider($httpClient);

        $provider->provideToken();
        $provider->provideToken();

        self::assertSame(1, $httpClient->getRequestsCount());
    }

    public function testItFailsWhenTheAuthorizationServerRejectsTheRequest(): void
    {
        $provider = $this->provider(new MockHttpClient([
            new MockResponse('{"error":"invalid_client"}', ['http_code' => 401]),
        ]));

        $this->expectException(AccessTokenRequestFailed::class);

        $provider->provideToken();
    }

    public function testItFailsWhenTheResponseIsMissingExpectedFields(): void
    {
        $provider = $this->provider(new MockHttpClient([
            new MockResponse(\json_encode(['token_type' => 'bearer'])),
        ]));

        $this->expectException(AccessTokenRequestFailed::class);

        $provider->provideToken();
    }

    public function testItSendsTheClientIdEchoedBackInsideTheScope(): void
    {
        $sentBody = null;
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$sentBody): MockResponse {
            $sentBody = $options['body'];

            return new MockResponse(\json_encode(['access_token' => 'abc123', 'expires_in' => 1500]));
        });

        $this->provider($httpClient)->provideToken();

        self::assertStringContainsString('scope=api_offresdemploiv2+o2dsoffre+application_test-client-id', $sentBody);
    }

    private function provider(MockHttpClient $httpClient): OAuth2AccessTokenProvider
    {
        return new OAuth2AccessTokenProvider(
            $httpClient,
            new ArrayAdapter(),
            'https://entreprise.francetravail.fr/connexion/oauth2/access_token',
            'test-client-id',
            'test-client-secret',
        );
    }
}
