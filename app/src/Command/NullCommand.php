<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NullCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:null');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('This does nothing.');

        return 0;
    }
}
