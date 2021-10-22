<?php

namespace Draw\Bundle\MessengerBundle\Command;

use Draw\Component\Messenger\Transport\ObsoleteMessageAwareInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PurgeExpiredMessageCommand extends Command
{
    public const DEFAULT_DELAY = '-1 month';

    private $transportLocator;
    private $transportNames;

    public function __construct(ContainerInterface $transportLocator, array $transportNames)
    {
        $this->transportLocator = $transportLocator;
        $this->transportNames = $transportNames;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('draw:messenger:purge-obsolete')
            ->setDescription('Purge obsolete message from transports.')
            ->addArgument('transport', InputArgument::OPTIONAL, 'Name of the transport to setup', null)
            ->addOption(
                'delay',
                null,
                InputOption::VALUE_OPTIONAL,
                'Records older than this date interval will be deleted.',
                self::DEFAULT_DELAY
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $delay = new \DateTime($input->getOption('delay'));
        $io = new SymfonyStyle($input, $output);

        $transportNames = $this->transportNames;
        // do we want to setup only one transport?
        if ($transport = $input->getArgument('transport')) {
            if (!$this->transportLocator->has($transport)) {
                throw new \RuntimeException(sprintf('The "%s" transport does not exist.', $transport));
            }
            $transportNames = [$transport];
        }

        foreach ($transportNames as $id => $transportName) {
            $transport = $this->transportLocator->get($transportName);
            if ($transport instanceof ObsoleteMessageAwareInterface) {
                $count = $transport->purgeObsoleteMessages($delay);
                $io->success(sprintf('The "%s" transport was purge successfully of "%s" message(s).', $transportName, $count));
            } else {
                $io->note(sprintf('The "%s" transport does not support purge obsolete messages.', $transportName));
            }
        }

        return 0;
    }
}
