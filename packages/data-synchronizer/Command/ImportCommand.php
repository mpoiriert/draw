<?php

namespace Draw\Component\DataSynchronizer\Command;

use Draw\Component\DataSynchronizer\DataSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'draw:data-synchronizer:import',
    description: 'Import data from a file to a database',
)]
class ImportCommand extends Command
{
    public function __construct(
        private DataSynchronizer $dataSynchronizer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('input-file', InputArgument::REQUIRED, 'Zip input file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dataSynchronizer->import(
            $input->getArgument('output-file'),
        );

        return Command::SUCCESS;
    }
}
