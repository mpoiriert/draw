<?php

namespace Draw\Bundle\UserBundle\EventListener;

use Draw\Bundle\UserBundle\Event\UserRequestInterceptedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserRequestInterceptedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UserRequestInterceptedEvent::class => 'errorOnUserRequestIntercepted',
        ];
    }

    public function errorOnUserRequestIntercepted(UserRequestInterceptedEvent $event)
    {
        $request = $event->getRequest();

        if ('json' !== $request->getRequestFormat()) {
            return;
        }

        throw new AccessDeniedHttpException('User request intercepted: '.$event->getReason());
    }
}
