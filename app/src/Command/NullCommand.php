<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:null',
    description: 'This command does nothing.',
)]
class NullCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'exit-code',
                null,
                InputOption::VALUE_REQUIRED,
                'The exit code to return.',
                Command::SUCCESS
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('This does nothing.');

        return (int) $input->getOption('exit-code');
    }
}
