<?php

namespace Draw\Component\Mailer\EventListener;

use Pelago\Emogrifier\CssInliner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

class EmailCssInlinerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => ['inlineEmailCss', -1],
        ];
    }

    public function inlineEmailCss(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if ($message instanceof Email && $htmlBody = $message->getHtmlBody()) {
            $message->html(CssInliner::fromHtml($htmlBody)->inlineCss()->render());
        }
    }
}
