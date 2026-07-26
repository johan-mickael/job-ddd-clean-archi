<?php

declare(strict_types=1);

namespace Search\Infrastructure\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'search:create-index', description: 'Create the job search index if it does not exist yet')]
final class CreateSearchIndexCommand extends Command
{
    public function __construct(
        private readonly Client $client,
        private readonly string $indexName,
        private readonly int $embeddingDimensions,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->indexExists()) {
            $io->comment(\sprintf('Index "%s" already exists, nothing to do.', $this->indexName));

            return Command::SUCCESS;
        }

        $this->client->indices()->create([
            'index' => $this->indexName,
            'body' => JobIndexMapping::body($this->embeddingDimensions),
        ]);

        $io->success(\sprintf('Index "%s" created.', $this->indexName));

        return Command::SUCCESS;
    }

    private function indexExists(): bool
    {
        try {
            $response = $this->client->indices()->exists(['index' => $this->indexName]);
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                return false;
            }

            throw $e;
        }

        return $response instanceof Elasticsearch && $response->asBool();
    }
}
