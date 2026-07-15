<?php

namespace Draw\Component\Mailer\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'draw:mailer:send-test-email',
    description: 'Send a test email.',
)]
class SendTestEmailCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('to', InputArgument::REQUIRED, 'Email to send to')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->mailer->send(
            new Email()
                ->subject('Test')
                ->text('This email as been sent as part of a test.')
                ->to($input->getArgument('to'))
        );

        return 0;
    }
}
