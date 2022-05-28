<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NullCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('app:null')
            ->setDescription('This command does nothing.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('This does nothing.');

        return static::SUCCESS;
    }
}
