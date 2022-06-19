<?php

namespace Draw\Component\Mailer\EventListener;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

class EmailSubjectFromHtmlTitleListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => ['assignSubjectFromHtmlTitle', -2],
        ];
    }

    public function assignSubjectFromHtmlTitle(MessageEvent $messageEvent): void
    {
        $message = $messageEvent->getMessage();
        if (!$message instanceof Email) {
            return;
        }

        if (null !== $message->getSubject()) {
            return;
        }

        if (!\is_string($htmlBody = $message->getHtmlBody())) {
            return;
        }

        $crawler = (new Crawler($htmlBody))->filter('html > head > title')->first();

        if (0 === \count($crawler)) {
            return;
        }

        if (empty($subject = $crawler->text())) {
            return;
        }

        $message->subject($subject);
    }
}
