<?php

namespace Draw\Component\Messenger\Broker\Command;

use Draw\Component\Messenger\Broker\Broker;
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
    public function __construct(
        private string $consolePath,
        private ProcessFactoryInterface $processFactory,
        private EventDispatcherInterface $eventDispatcher
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
                'The number of concurrent consumer you want to run',
                1
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

        $concurrent = (int) $input->getOption('concurrent');
        if ($concurrent <= 0) {
            throw new InvalidOptionException('Concurrent value ['.$concurrent.'] is invalid. Must be 1 or greater');
        }

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
}
