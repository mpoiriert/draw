<?php

declare(strict_types=1);

namespace Draw\Component\Mailer\Tests\Stub\EmailWriter;

use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Message;

class EmailWriterStub implements EmailWriterInterface
{
    #[\Override]
    public static function getForEmails(): array
    {
        return [];
    }

    public function method1(Message $message, Envelope $envelope): void
    {
    }

    public function method2(Message $message, Envelope $envelope): void
    {
    }
}
