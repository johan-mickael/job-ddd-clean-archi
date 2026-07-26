<?php

declare(strict_types=1);

namespace Ingestion\Presentation\Console;

use Ingestion\Application\RunScheduledSync\RunScheduledSyncHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ingestion:sync', description: 'Sync job offers from France Travail and expire delisted ones')]
final class RunScheduledSyncCommand extends Command
{
    public function __construct(private readonly RunScheduledSyncHandler $runScheduledSync)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        ($this->runScheduledSync)();

        $io->success('Sync complete.');

        return Command::SUCCESS;
    }
}
