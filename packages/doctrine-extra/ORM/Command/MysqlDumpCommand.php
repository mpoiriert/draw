<?php

namespace Draw\DoctrineExtra\ORM\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class MysqlDumpCommand extends Command
{
    public function __construct(private ManagerRegistry $ormManagerRegistry)
    {
        parent::__construct('draw:doctrine:mysql-dump');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Dump a mysql database to a file')
            ->addArgument('file', InputArgument::REQUIRED, 'The file path to dump')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'The connection to use', 'default')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connectionParameter = $this->ormManagerRegistry->getConnection($input->getOption('connection'))
            ->getParams()['primary']
        ;

        Process::fromShellCommandline(
            \sprintf(
                'mysqldump -h %s -P %s -u %s %s %s > %s',
                $connectionParameter['host'],
                $connectionParameter['port'],
                $connectionParameter['user'],
                empty($connectionParameter['password']) ? '' : '-p'.$connectionParameter['password'],
                $connectionParameter['dbname'],
                $input->getArgument('file'),
            ),
            timeout: 600
        )->mustRun();

        return Command::SUCCESS;
    }
}
