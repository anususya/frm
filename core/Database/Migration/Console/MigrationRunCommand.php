<?php

declare(strict_types=1);

namespace Core\Database\Migration\Console;

use Core\Database\Migration\MigrationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationRunCommand extends Command
{
    protected string $defaultName = 'migration:run';

    public function __construct(
        private readonly MigrationService $migration
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // Set command description
        $this->setName($this->defaultName)->setDescription('Run database migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Run migrations
        $output->writeln('<info>Running migrations...</info>');
        try {
            $this->migration->run();
            $output->writeln('<info>Migrations executed successfully!</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Error occurred: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
