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

        switch (true) {
            case $message->getSubject():
            case !($body = $message->getHtmlBody()):
            case !\count($crawler = (new Crawler($body))->filter('html > head > title')->first()):
            case !($subject = $crawler->text()):
                return;
        }

        $message->subject($subject);
    }
}
