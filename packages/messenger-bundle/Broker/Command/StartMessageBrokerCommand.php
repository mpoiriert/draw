<?php

namespace Draw\Bundle\MessengerBundle\Broker\Command;

use Draw\Bundle\MessengerBundle\Broker\Broker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StartMessageBrokerCommand extends Command
{
    private $broker;

    public function __construct(Broker $broker)
    {
        $this->broker = $broker;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('draw:messenger:start-broker')
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
        if (0 === $timeout) {
            throw new InvalidOptionException('Timeout value ['.$timeout.'] is invalid. Must be 0 or greater');
        }

        $io->success('Broker started. Concurrency '.$concurrent.', timeout '.$timeout);

        $this->broker->start($concurrent, $timeout);

        $io->success('Broker stopped.');

        return 0;
    }
}
