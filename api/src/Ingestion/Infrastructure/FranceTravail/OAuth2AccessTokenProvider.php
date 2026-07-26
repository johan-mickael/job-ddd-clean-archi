<?php

declare(strict_types=1);

namespace Ingestion\Infrastructure\FranceTravail;

use Ingestion\Infrastructure\FranceTravail\Exception\AccessTokenRequestFailed;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OAuth2AccessTokenProvider implements AccessTokenProvider
{
    private const string CACHE_KEY = 'ingestion.france_travail.access_token';
    private const int EXPIRY_SAFETY_MARGIN_SECONDS = 60;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly string $tokenUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $scope,
    ) {
    }

    public function provideToken(): AccessToken
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): AccessToken {
            $token = $this->requestToken();
            $item->expiresAt($token->expiresAt->modify(\sprintf('-%d seconds', self::EXPIRY_SAFETY_MARGIN_SECONDS)));

            return $token;
        });
    }

    private function requestToken(): AccessToken
    {
        try {
            $response = $this->httpClient->request('POST', $this->tokenUrl, [
                'query' => ['realm' => '/partenaire'],
                'body' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => $this->scope,
                ],
            ]);
            $payload = $response->toArray();
        } catch (HttpClientExceptionInterface $e) {
            throw AccessTokenRequestFailed::authenticationFailed($e);
        }

        if (!isset($payload['access_token'], $payload['expires_in'])
            || !\is_string($payload['access_token'])
            || !\is_int($payload['expires_in'])
        ) {
            throw AccessTokenRequestFailed::malformedResponse();
        }

        return new AccessToken(
            $payload['access_token'],
            new \DateTimeImmutable(\sprintf('+%d seconds', $payload['expires_in'])),
        );
    }
}
