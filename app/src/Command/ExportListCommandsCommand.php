<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportListCommandsCommand extends Command
{
    protected static $defaultName = 'app:list-commands';

    protected function configure(): void
    {
        $this
          ->setDescription('List all available Symfony commands with their documentation.')
          ->setHelp('This command lists all available Symfony commands and their descriptions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commands = $this->getApplication()->all();
        $commandList = [];

        foreach ($commands as $name => $command) {
            $description = $this->sanitizeText($command->getDescription());
            $synopsis = $this->sanitizeText($command->getSynopsis());
            $helper = $this->sanitizeText($command->getHelp());

            $commandList[$name]['description'] = $description;
            $commandList[$name]['synopsis'] = $synopsis;
            $commandList[$name]['helper'] = $helper;
        }

        $jsonOutput = json_encode($commandList, \JSON_PRETTY_PRINT);
        $output->writeln($jsonOutput);

        return 0;
    }

    private function sanitizeText($text)
    {
        $output = strip_tags($text);
        $output = preg_replace('/\s+/', ' ', $output);
        $output = str_replace('"', "'", $output);
        $output = trim($output);

        return $output;
    }
}
