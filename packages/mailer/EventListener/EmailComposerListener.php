<?php

namespace Draw\Component\Mailer\EventListener;

use Draw\Component\Mailer\EmailComposer;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\Message;

class EmailComposerListener
{
    public function __construct(private EmailComposer $emailComposer)
    {
    }

    #[AsEventListener(priority: 200)]
    public function composeMessage(MessageEvent $event): void
    {
        if ($event->isQueued()) {
            return;
        }

        $message = $event->getMessage();
        if (!$message instanceof Message) {
            return;
        }

        $headers = $message->getHeaders();
        if ($headers->has('X-DrawEmail')) {
            return;
        }

        $headers->add(new UnstructuredHeader('X-DrawEmail', '1'));

        $this->emailComposer->compose($message, $event->getEnvelope());
    }
}
