<?php

namespace Draw\Component\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'app:list-commands')]
class ExportListCommandsCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('List all available Symfony commands with their documentation.')
            ->addArgument('Path', InputArgument::REQUIRED, 'Output folder path')
            ->addArgument('Filename', InputArgument::REQUIRED, 'Output filename');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filesystem = new Filesystem();
        $folderPath = $input->getArgument('Path');
        $resultFilePath = $folderPath.$input->getArgument('Filename');
        $application = $this->getApplication();

        $this->validateOutputPathFile($filesystem, $folderPath);

        foreach ($application->all() as $command) {
            $process = new Process(['bin/console', $command->getName(), '--help']);
            $process->mustRun();

            try {
                if ($process->isSuccessful()) {
                    file_put_contents($resultFilePath, $this->addBreakLineAtTheEnd($this->addNameToTheFullDescription($this->generateBlockText($command->getName()), $process->getOutput())), \FILE_APPEND);
                } else {
                    $io->warning(sprintf('Error running command: %s', $command->getName()));
                }
            } catch (\Throwable $th) {
                $io->error('An error occurred: '.$th->getMessage());
                continue;
            }
        }

        $io->success('Export completed');

        return Command::SUCCESS;
    }

    private function generateBlockText(string $title, int $width = 30, string $borderChar = '#', string $paddingChar = ' '): string
    {
        $titleLength = \strlen($title);

        if ($titleLength > $width - 4) {
            $width = $titleLength + 4;
        }

        $boderLine = str_repeat($borderChar, $width);
        $titleLine = $borderChar.' '.str_pad($title, $width - 4, $paddingChar, \STR_PAD_BOTH).' '.$borderChar;

        return $boderLine.\PHP_EOL.$titleLine.\PHP_EOL.$boderLine;
    }

    private function addNameToTheFullDescription(string $name, string $fullDescription): string
    {
        return $name.\PHP_EOL.$fullDescription;
    }

    private function addBreakLineAtTheEnd(string $text): string
    {
        return $text.\PHP_EOL;
    }

    private function validateOutputPathFile(Filesystem $filesystem, string $path): void
    {
        if (!$filesystem->exists($path)) {
            $filesystem->mkdir($path);
        }
    }

    // private function generateBlockText($title): string
    // {
    //     return 'Name: '.\PHP_EOL.$title;
    // }

    // private function addNameToTheFullDescription($name, $fullDescription): string
    // {
    //     return $name.\PHP_EOL.$fullDescription;
    // }

    // private function strToJson($input): array
    // {
    //     $lines = explode("\n", $input);
    //     $indexes = ['Name:', 'Description:', 'Usage:', 'Arguments:', 'Options:', 'Help:'];
    //     $result = [];
    //     $currentKey = '';
    //     $subArray = [];
    //     foreach ($lines as $line) {
    //         $line = trim($line);

    //         if (false !== \in_array($line, $indexes)) {
    //             if (!empty($subArray)) {
    //                 $result[$currentKey] = $subArray;
    //                 $subArray = [];
    //             }
    //             $currentKey = trim($line);
    //         } else {
    //             $subArray[] = $line;
    //         }
    //     }

    //     if (!empty($subArray)) {
    //         $result[$currentKey] = $subArray;
    //     }

    //     foreach ($result as $key => $values) {
    //         foreach ($values as $index => $value) {
    //             $result[$key][$index] = trim($value);
    //         }
    //     }

    //     foreach ($indexes as $index) {
    //         if (isset($result[$index])) {
    //             $result[$index] = array_filter($result[$index]);
    //         }
    //     }

    //     return $result;
    // }
}
