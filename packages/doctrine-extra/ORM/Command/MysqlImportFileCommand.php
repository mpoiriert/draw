<?php

namespace Draw\DoctrineExtra\ORM\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class MysqlImportFileCommand extends Command
{
    public function __construct(private ManagerRegistry $ormManagerRegistry)
    {
        parent::__construct('draw:doctrine:mysql-import-file');
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
        $connectionParameter = $this->ormManagerRegistry
            ->getConnection($input->getOption('connection'))
            ->getParams()['primary'];

        foreach ($input->getArgument('files') as $file) {
            Process::fromShellCommandline(
                sprintf(
                    'mysql -h %s -P %s -u %s %s %s < %s',
                    $connectionParameter['host'],
                    $connectionParameter['port'],
                    $connectionParameter['user'],
                    empty($connectionParameter['password']) ? '' : '-p'.$connectionParameter['password'],
                    $connectionParameter['dbname'],
                    $file
                ),
                timeout: 600
            )->mustRun();
        }

        return Command::SUCCESS;
    }
}
