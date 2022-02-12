<?php

namespace Draw\Bundle\UserBundle\Listener;

use Draw\Bundle\UserBundle\Event\UserRequestInterceptedEvent;
use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserRequestInterceptorSubscriber implements EventSubscriberInterface
{
    private $eventDispatcher;

    private $security;

    public static function getSubscribedEvents()
    {
        yield RequestEvent::class => 'handleRequestEvent';
    }

    public function __construct(EventDispatcherInterface $eventDispatcher, Security $security)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
    }

    public function handleRequestEvent(RequestEvent $requestEvent)
    {
        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        $request = $requestEvent->getRequest();

        $event = $this->eventDispatcher->dispatch(new UserRequestInterceptionEvent(
            $user,
            $request
        ));

        if ($response = $event->getResponse()) {
            $this->eventDispatcher->dispatch(new UserRequestInterceptedEvent(
                $user,
                $request,
                $response,
                $event->getReason()
            ));

            $requestEvent->setResponse($response);

            return;
        }

        if ($event->allowHandlingRequest()) {
            $requestEvent->stopPropagation();
        }
    }
}
