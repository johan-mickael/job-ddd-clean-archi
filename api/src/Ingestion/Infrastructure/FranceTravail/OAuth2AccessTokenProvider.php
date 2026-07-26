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

    // France Travail requires the client's own id echoed back inside the
    // scope string (`application_{client_id}`) - it's not a free parameter.
    private const string SCOPE_TEMPLATE = 'api_offresdemploiv2 o2dsoffre application_%s';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly string $tokenUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

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
                    'scope' => \sprintf(self::SCOPE_TEMPLATE, $this->clientId),
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
