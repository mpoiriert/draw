<?php namespace Draw\Bundle\PostOfficeBundle\Listener;

use Pelago\Emogrifier\CssInliner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

class EmailCssInlinerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            MessageEvent::class =>
                ['inlineEmailCss', -1]
        ];
    }

    public function inlineEmailCss(MessageEvent $event)
    {
        $message = $event->getMessage();

        if($message instanceof Email && $message->getHtmlBody()) {
            $message->html(CssInliner::fromHtml($message->getHtmlBody())->inlineCss()->render());
        }
    }
}