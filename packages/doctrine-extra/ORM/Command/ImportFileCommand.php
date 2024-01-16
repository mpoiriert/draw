<?php

namespace Draw\DoctrineExtra\ORM\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportFileCommand extends Command
{
    public function __construct(private ManagerRegistry $ormManagerRegistry)
    {
        parent::__construct('draw:doctrine:import-file');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import a files in the database')
            ->addArgument('files', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The files to import', null)
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'The connection to use', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->ormManagerRegistry->getConnection($input->getOption('connection'));

        foreach ($input->getArgument('files') as $file) {
            $connection->executeQuery(file_get_contents($file));
        }

        return Command::SUCCESS;
    }
}
