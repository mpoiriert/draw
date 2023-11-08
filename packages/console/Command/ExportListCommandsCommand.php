<?php

namespace Draw\Component\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'app:list-commands')]
class ExportListCommandsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('List all available Symfony commands with their documentation.')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file', 'commands_help.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();
        $outputFile = $input->getOption('output');
        $data = [];

        foreach ($application->all() as $command) {
            $process = new Process(['bin/console', $command->getName(), '--help']);
            $process->mustRun();

            if ($process->isSuccessful()) {
                $data[] = $this->strToJson($this->addNameToTheFullDescription($this->generateBlockText($command->getName()), $process->getOutput()));
            } else {
                $output->writeln(sprintf('Error running command: %s', $command->getName()));
            }
        }

        file_put_contents($outputFile, json_encode($data, \JSON_PRETTY_PRINT), \FILE_APPEND);

        $io = new SymfonyStyle($input, $output);
        $io->title('Export completed');

        return Command::SUCCESS;
    }

    private function generateBlockText($title): string
    {
        return 'Name: '.\PHP_EOL.$title;
    }

    private function addNameToTheFullDescription($name, $fullDescription): string
    {
        return $name.\PHP_EOL.$fullDescription;
    }

    private function strToJson($input): array
    {
        $lines = explode("\n", $input);
        $indexes = ['Name:', 'Description:', 'Usage:', 'Arguments:', 'Options:', 'Help:'];
        $result = [];
        $currentKey = '';
        $subArray = [];
        foreach ($lines as $line) {
            $line = trim($line);

            if (false !== \in_array($line, $indexes)) {
                if (!empty($subArray)) {
                    $result[$currentKey] = $subArray;
                    $subArray = [];
                }
                $currentKey = trim($line);
            } else {
                $subArray[] = $line;
            }
        }

        if (!empty($subArray)) {
            $result[$currentKey] = $subArray;
        }

        foreach ($result as $key => $values) {
            foreach ($values as $index => $value) {
                $result[$key][$index] = trim($value);
            }
        }

        foreach ($indexes as $index) {
            if (isset($result[$index])) {
                $result[$index] = array_filter($result[$index]);
            }
        }

        return $result;
    }
}
