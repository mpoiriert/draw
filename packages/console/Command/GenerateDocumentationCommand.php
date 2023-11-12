<?php

namespace Draw\Component\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'draw:console:generate-documentation')]
class GenerateDocumentationCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Generate a documentation for all the command of the application.')
            ->addArgument('path', InputArgument::REQUIRED, 'The path where the documentation will be generated.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Generate documentation');

        $filePath = $input->getArgument('path');

        $filesystem = new Filesystem();

        $directoryPath = \dirname($filePath);

        if (!$filesystem->exists($directoryPath)) {
            $filesystem->mkdir($directoryPath);
        }

        file_put_contents($filePath, '');

        $file = fopen($filePath, 'w');

        $commandOutput = new StreamOutput($file);

        $commands = $this->getApplication()->all();

        $progress = $io->createProgressBar(\count($commands));
        $progress->setFormat(ProgressBar::FORMAT_DEBUG);

        $descriptionHelper = new DescriptorHelper();

        foreach ($commands as $command) {
            $descriptionHelper->describe(
                $commandOutput,
                $command,
                [
                    'format' => 'md',
                    'raw_text' => false,
                ]
            );

            fwrite($file, "\n\n");

            $progress->advance();
        }

        $progress->finish();

        $io->newLine(2);
        $io->success('Export completed');

        return Command::SUCCESS;
    }
}
