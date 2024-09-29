<?php

namespace Draw\Component\Tester\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestsCoverageCheckCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('draw:tester:coverage-check')
            ->setDescription('Check the code coverage by the tests')
            ->addArgument('clover-xlm-file-path', InputArgument::REQUIRED, 'Clover report file path')
            ->addArgument('coverage', InputArgument::REQUIRED, 'Required coverage in percentage');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $inputFile = realpath($input->getArgument('clover-xlm-file-path'));
        $percentage = (float) $input->getArgument('coverage');
        if (!is_file($inputFile)) {
            throw new \InvalidArgumentException(\sprintf('Invalid input file provided "%s"', $inputFile));
        }
        if (!$percentage) {
            throw new \InvalidArgumentException(\sprintf('Invalid coverage percentage value "%s"', $input->getArgument('coverage')));
        }

        $io->title('Automation test coverage check');

        $io->note('Coverage threshold: '.$percentage);
        $io->note('Against file: '.$inputFile);

        $xml = new \SimpleXMLElement(file_get_contents($inputFile));
        $metrics = $xml->xpath('//file/metrics');
        $totalElements = 0;
        $checkedElements = 0;
        foreach ($metrics as $metric) {
            $totalElements += (int) $metric['statements'];
            $checkedElements += (int) $metric['coveredstatements'];
        }

        $coverage = ($checkedElements / $totalElements) * 100;
        if ($coverage < $percentage) {
            $io->error(\sprintf(
                'Code coverage is %.02f%%, which is below the accepted %d%%',
                $coverage,
                $percentage
            ));

            return 1;
        }

        $io->success(\sprintf('Code coverage is %.02f%%', $coverage));

        return 0;
    }
}
