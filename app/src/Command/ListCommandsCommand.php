<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DescriptorHelper;

class ListCommandsCommand extends Command
{
  protected static $defaultName = 'app:list-commands';

  protected function configure()
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
      $description = $command->getDescription();
      $commandList[$name] = $description;
    }


    $jsonOutput = json_encode($commandList, JSON_PRETTY_PRINT);

    $output->writeln($jsonOutput);

    return 0;
  }

}
