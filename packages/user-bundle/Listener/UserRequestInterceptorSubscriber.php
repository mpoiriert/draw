<?php

namespace Draw\Bundle\UserBundle\Listener;

use Draw\Bundle\UserBundle\Event\UserRequestInterceptedEvent;
use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserRequestInterceptorSubscriber implements EventSubscriberInterface
{
    private const REQUEST_INTERCEPTION_ORIGINAL_URL = 'request-interception-original-url';

    private const INTERCEPTION_REASON = 'original_request_url';

    private $eventDispatcher;

    private $security;

    public static function getSubscribedEvents()
    {
        yield RequestEvent::class => 'handleRequestEvent';
        yield UserRequestInterceptedEvent::class => [
            ['handleUserRequestInterceptedEventForRedirect', 10000],
        ];
        yield UserRequestInterceptionEvent::class => [
            ['handleUserRequestInterceptionEventForRedirect', -10000],
        ];
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

        if ($event->getAllowHandlingRequest()) {
            $requestEvent->stopPropagation();
        }
    }

    public function handleUserRequestInterceptionEventForRedirect(UserRequestInterceptionEvent $event): void
    {
        $request = $event->getRequest();

        switch (true) {
            case !$request->hasSession():
            case null === $session = $request->getSession():
            case !$session->has(self::REQUEST_INTERCEPTION_ORIGINAL_URL):
                return;
        }

        $redirect = $session->get(self::REQUEST_INTERCEPTION_ORIGINAL_URL);
        $session->remove(self::REQUEST_INTERCEPTION_ORIGINAL_URL);
        $event->setResponse(
            new RedirectResponse($redirect),
            self::INTERCEPTION_REASON
        );
    }

    public function handleUserRequestInterceptedEventForRedirect(UserRequestInterceptedEvent $event): void
    {
        $request = $event->getRequest();

        switch (true) {
            case self::INTERCEPTION_REASON === $event->getReason():
            case !$request->hasSession():
            case null === $session = $request->getSession():
            case $session->has(self::REQUEST_INTERCEPTION_ORIGINAL_URL):
                return;
        }

        $session->set(self::REQUEST_INTERCEPTION_ORIGINAL_URL, $request->getUri());
    }
}
