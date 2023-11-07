<?php

namespace Draw\Component\Console\Command;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:list-commands')]
class ExportListCommandsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('List all available Symfony commands with their documentation.')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file', 'commands_help.txt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();
        $outputFile = $input->getOption('output');

        foreach ($application->all() as $command) {
            $process = new Process(['bin/console', $command->getName(), '--help']);
            $process->mustRun();

            if ($process->isSuccessful()) {
                file_put_contents($outputFile, $this->addNameToTheFullDescription($this->generateBlockText($command->getName()), $process->getOutput()), FILE_APPEND);
            } else {
                $output->writeln(sprintf('Error running command: %s', $command->getName()));
            }
        }

        $io = new SymfonyStyle($input, $output);
        $io->title('Export completed');
        return Command::SUCCESS;
    }

    private function generateBlockText($title, $width = 30, $borderChar = '#', $paddingChar = ' '): string
    {
        $titleLength = strlen($title);

        if ($titleLength > $width - 4) {
            $width = $titleLength + 4;
        }

        $topLine = str_repeat($borderChar, $width);
        $titleLine = $borderChar . ' ' . str_pad($title, $width - 4, $paddingChar, STR_PAD_BOTH) . ' ' . $borderChar;
        $bottomLine = $topLine;
        $blockText = $topLine . PHP_EOL . $titleLine . PHP_EOL . $bottomLine;
        return $blockText;
    }

    private function addNameToTheFullDescription($name, $fullDescription): string
    {
        return $name . PHP_EOL . $fullDescription;
    }


// option with json
   
    // private function generateBlockText($title): string
    // {
    //     return 'Name: ' . PHP_EOL .  $title;
    // }
    // private function addNameToTheFullDescription($name, $fullDescription): string
    // {
    //     return $name . PHP_EOL . $fullDescription;
    // }
    // private function strToJson($input): array
    // {
    //     $lines = explode("\n", $input);
    //     $result = [];
    //     $os = ['Name:', 'Description:', 'Usage:', 'Arguments:', 'Options:', 'Help:'];
    //     $currentSection = '';
    //     foreach ($lines as $line) {
    //         $line = trim($line);
    //         if (empty($line)) {
    //             continue;
    //         }
    //         if (in_array($line, $os) !== false) {
    //             list($key, $value) = array_map('trim', explode(':', $line, 2));
    //             $currentSection = $line;
    //             $currentSection = str_replace(':', '', $currentSection);
    //             $currentSection = ucfirst(strtolower($currentSection));
    //             $result[$currentSection] = '';
    //         } elseif (!empty($currentSection)) {
    //             $result[$currentSection] .= ' ' . $line;
    //         }
    //     }
    //     foreach ($result as $key => $value) {
    //         $result[$key] = trim($value);
    //     }
    //     return $result;
    // }
}
