<?php

declare(strict_types=1);

namespace Search\Presentation\Console;

use Search\Application\ReindexAllJobs\ReindexAllJobs;
use Search\Application\ReindexAllJobs\ReindexAllJobsHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'search:reindex', description: 'Rebuild the search index from every published job in Postgres')]
final class ReindexAllJobsCommand extends Command
{
    public function __construct(private readonly ReindexAllJobsHandler $reindexAllJobs)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = ($this->reindexAllJobs)(new ReindexAllJobs());

        $io->success(\sprintf('Reindexed %d published job(s).', $count));

        return Command::SUCCESS;
    }
}
