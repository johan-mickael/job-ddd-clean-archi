<?php

declare(strict_types=1);

namespace Search\Infrastructure\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

final class ElasticsearchClientFactory
{
    public static function create(string $dsn): Client
    {
        return ClientBuilder::create()->setHosts([$dsn])->build();
    }
}
