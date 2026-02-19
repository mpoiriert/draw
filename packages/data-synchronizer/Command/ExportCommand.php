<?php

namespace Draw\Component\DataSynchronizer\Command;

use Draw\Component\DataSynchronizer\DataSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'draw:data-synchronizer:export',
    description: 'Export data from the database to a file',
)]
class ExportCommand extends Command
{
    public function __construct(
        private DataSynchronizer $dataSynchronizer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('output-file', InputArgument::REQUIRED, 'Zip output file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $this->dataSynchronizer->export();

        if (!rename($file, $input->getArgument('output-file'))) {
            $output->writeln('<error>Unable to rename file</error>');
            $output->writeln('<error>File: '.$file.'</error>');
            $output->writeln('<error>Output file: '.$input->getArgument('output-file').'</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
