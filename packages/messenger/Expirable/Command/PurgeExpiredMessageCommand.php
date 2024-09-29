<?php

namespace Draw\Component\Messenger\Expirable\Command;

use Draw\Component\Messenger\Expirable\PurgeableTransportInterface;
use Draw\Component\Messenger\Searchable\TransportRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PurgeExpiredMessageCommand extends Command
{
    final public const DEFAULT_DELAY = '-1 month';

    public function __construct(private TransportRepository $transportRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('draw:messenger:purge-obsolete-messages')
            ->setDescription('Purge obsolete message from transports.')
            ->addArgument('transport', InputArgument::OPTIONAL, 'Name of the transport to setup', null)
            ->addOption(
                'delay',
                null,
                InputOption::VALUE_OPTIONAL,
                'Records older than this date (compatible with strtotime) will be deleted.',
                self::DEFAULT_DELAY
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $delay = new \DateTime($input->getOption('delay'));
        $io = new SymfonyStyle($input, $output);

        // do we want to setup only one transport?
        if ($transportName = $input->getArgument('transport')) {
            if (!$this->transportRepository->has($transportName)) {
                throw new \RuntimeException(\sprintf('The "%s" transport does not exist.', $transportName));
            }
            $transportNames = [$transportName];
        } else {
            $transportNames = $this->transportRepository->getTransportNames();
        }

        foreach ($transportNames as $transportName) {
            $transport = $this->transportRepository->get($transportName);
            if ($transport instanceof PurgeableTransportInterface) {
                $count = $transport->purgeObsoleteMessages($delay);
                $io->success(\sprintf('The "%s" transport was purge successfully of "%s" message(s).', $transportName, $count));
            } else {
                $io->note(\sprintf('The "%s" transport does not support purge obsolete messages.', $transportName));
            }
        }

        return 0;
    }
}
