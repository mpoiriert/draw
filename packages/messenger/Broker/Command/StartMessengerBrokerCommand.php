<?php

namespace Draw\Component\Messenger\Broker\Command;

use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Counter\CpuCounter;
use Draw\Contracts\Process\ProcessFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class StartMessengerBrokerCommand extends Command
{
    private const OPTION_VALUE_CONCURRENT_AUTO = 'auto';

    public function __construct(
        private string $consolePath,
        private ProcessFactoryInterface $processFactory,
        private EventDispatcherInterface $eventDispatcher,
        private CpuCounter $cpuCounter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('draw:messenger:start-broker')
            ->setDescription('Start multiple messenger:consume base on concurrent option. Automatically restart them if stopped.')
            ->addOption(
                'context',
                null,
                InputOption::VALUE_REQUIRED,
                'The context you want this broker to run. Default to "default"',
                'default'
            )
            ->addOption(
                'concurrent',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'The number of concurrent consumers you want to run; use "%s" to use the auto calculation of CPU',
                    self::OPTION_VALUE_CONCURRENT_AUTO
                ),
                1
            )
            ->addOption(
                'processes-per-core',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'The number of processes per CPU (used only if "concurrent" is set to "%s")',
                    self::OPTION_VALUE_CONCURRENT_AUTO
                ),
                1
            )
            ->addOption(
                'minimum-processes',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Minimum number of processes (used only if "concurrent" is set to "%s")',
                    self::OPTION_VALUE_CONCURRENT_AUTO
                ),
                1
            )
            ->addOption(
                'maximum-processes',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Maximum number of processes (used only if "concurrent" is set to "%s")',
                    self::OPTION_VALUE_CONCURRENT_AUTO
                )
            )
            ->addOption(
                'timeout',
                null,
                InputOption::VALUE_REQUIRED,
                'The timeout in seconds before sending a sig kill when stopping. Default to 10.',
                10
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $concurrent = $this->getConcurrent($input);

        $timeout = (int) $input->getOption('timeout');
        if ($timeout < 0) {
            throw new InvalidOptionException('Timeout value ['.$timeout.'] is invalid. Must be 0 or greater');
        }

        $io->success('Broker starting.');
        $io->note('Concurrency '.$concurrent);
        $io->note('Timeout '.$timeout);

        $this->createBroker($input->getOption('context'))->start($concurrent, $timeout);

        $io->success('Broker stopped.');

        return 0;
    }

    protected function createBroker(string $context): Broker
    {
        return new Broker($context, $this->consolePath, $this->processFactory, $this->eventDispatcher);
    }

    private function getConcurrent(InputInterface $input): int
    {
        $concurrent = $input->getOption('concurrent');
        if (self::OPTION_VALUE_CONCURRENT_AUTO === $concurrent) {
            return $this->calculateAutoConcurrent($input);
        }

        $concurrent = (int) $concurrent;
        if ($concurrent <= 0) {
            throw new InvalidOptionException(sprintf(
                'Concurrent value [%d] is invalid. Must be 1 or greater',
                $concurrent
            ));
        }

        return $concurrent;
    }

    private function calculateAutoConcurrent(InputInterface $input): int
    {
        $processesPerCore = (float) $input->getOption('processes-per-core');
        if ($processesPerCore <= 0) {
            throw new InvalidOptionException(sprintf(
                'Processes per core value [%f] is invalid. Must be greater than 0',
                $processesPerCore
            ));
        }

        $minProcesses = (int) $input->getOption('minimum-processes');
        if ($minProcesses <= 0) {
            throw new InvalidOptionException(sprintf(
                'Minimum processes value [%d] is invalid. Must be greater than 0',
                $minProcesses
            ));
        }

        $process = max([$minProcesses, (int) round($processesPerCore * $this->cpuCounter->count())]);

        if ($maxProcesses = null === $input->getOption('maximum-processes')) {
            return $process;
        }

        $maxProcesses = (int) $input->getOption('maximum-processes');
        if ($maxProcesses <= 0) {
            throw new InvalidOptionException(sprintf(
                'Maximum processes value [%d] is invalid. Must be greater than 0',
                $maxProcesses
            ));
        }

        return min([$maxProcesses, $process]);
    }
}
