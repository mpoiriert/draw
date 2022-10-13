<?php

namespace Draw\Component\Security\Core\EventListener;

use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SystemMessengerAuthenticatorListener implements EventSubscriberInterface
{
    private SystemAuthenticatorInterface $systemAuthenticator;

    private TokenStorageInterface $tokenStorage;

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageReceivedEvent::class => 'connectSystem',
        ];
    }

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SystemAuthenticatorInterface $systemAuthenticator
    ) {
        $this->systemAuthenticator = $systemAuthenticator;
        $this->tokenStorage = $tokenStorage;
    }

    public function connectSystem(): void
    {
        if (null !== $this->tokenStorage->getToken()) {
            return;
        }

        $this->tokenStorage->setToken($this->systemAuthenticator->getTokenForSystem());
    }
}
