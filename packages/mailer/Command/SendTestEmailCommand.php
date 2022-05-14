<?php

namespace Draw\Component\Mailer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendTestEmailCommand extends Command
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('draw:mailer:send-test-email')
            ->setDescription('Send a test email.')
            ->addArgument('to', InputArgument::REQUIRED, 'Email to send to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->mailer->send(
            (new Email())
                ->subject('Test')
                ->text('This email as been sent as part of a test.')
                ->to($input->getArgument('to'))
        );

        return 0;
    }
}
