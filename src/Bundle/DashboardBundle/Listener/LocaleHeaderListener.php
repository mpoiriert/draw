<?php

namespace Draw\Bundle\DashboardBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleHeaderListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => ['setRequestLocale', 15], // Just before the LocaleLister but after the router
        ];
    }

    public function setRequestLocale(RequestEvent $requestEvent)
    {
        $request = $requestEvent->getRequest();
        if (!$request->getLocale()) {
            return;
        }

        if (!$locale = $request->headers->get('X-Locale')) {
            return;
        }
        $request->setLocale($locale);
    }
}
