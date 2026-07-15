<?php

namespace Draw\Bundle\UserBundle\Command;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\Message\RefreshUserLockMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'draw:user:refresh-user-locks',
    description: <<<'TEXT'
        Send a [RefreshUserLockMessage] for all user.
        Configure you messenger routing properly otherwise it will be sync
        TEXT,
)]
class RefreshUserLocksCommand extends Command
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private EntityRepository $drawUserEntityRepository,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Sending [RefreshUserLockMessage] messages');

        $progressBar = $io->createProgressBar($this->drawUserEntityRepository->count([]));

        $rows = $this->drawUserEntityRepository->createQueryBuilder('user')
            ->select('user.id')
            ->getQuery()
            ->execute()
        ;

        foreach ($rows as $row) {
            $this->messageBus->dispatch(new RefreshUserLockMessage($row['id']));
            $progressBar->advance();
        }

        $io->newLine(2);

        $io->success('Messages sent!');

        return 0;
    }
}
