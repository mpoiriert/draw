<?php

namespace Draw\Bundle\OpenApiBundle\Request\Listener;

use Draw\Bundle\UserBundle\Event\UserRequestInterceptedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserRequestInterceptedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        yield UserRequestInterceptedEvent::class => 'errorOnUserRequestIntercepted';
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
