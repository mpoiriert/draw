<?php

namespace Draw\Bundle\UserBundle\EventListener;

use Draw\Bundle\UserBundle\Event\UserRequestInterceptedEvent;
use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Draw\Component\Security\Core\Security;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserRequestInterceptorListener implements EventSubscriberInterface
{
    private const REQUEST_INTERCEPTION_ORIGINAL_URL = 'request-interception-original-url';

    private const INTERCEPTION_REASON = 'original_request_url';

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'handleRequestEvent',
            UserRequestInterceptedEvent::class => [
                ['handleUserRequestInterceptedEventForRedirect', 10000],
            ],
            UserRequestInterceptionEvent::class => [
                ['handleUserRequestInterceptionEventForRedirect', -10000],
            ],
        ];
    }

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private Security $security,
        private ?FirewallMap $firewallMap,
    ) {
    }

    public function handleRequestEvent(RequestEvent $requestEvent): void
    {
        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        if (HttpKernelInterface::SUB_REQUEST === $requestEvent->getRequestType()) {
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

        if (!$session = $this->getAccessibleSession($request)) {
            return;
        }

        if (!$session->has(self::REQUEST_INTERCEPTION_ORIGINAL_URL)) {
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

        if (self::INTERCEPTION_REASON === $event->getReason()) {
            return;
        }

        if (!$session = $this->getAccessibleSession($request)) {
            return;
        }

        if ($session->has(self::REQUEST_INTERCEPTION_ORIGINAL_URL)) {
            return;
        }

        $session->set(self::REQUEST_INTERCEPTION_ORIGINAL_URL, $request->getUri());
    }

    private function getAccessibleSession(Request $request): ?SessionInterface
    {
        if ($this->firewallMap) {
            $firewallConfig = $this->firewallMap->getFirewallConfig($request);
            if ($firewallConfig->isStateless()) {
                return null;
            }
        }

        if (!$request->hasSession(true)) {
            return null;
        }

        return $request->getSession();
    }
}
