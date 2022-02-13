<?php

namespace Draw\Bundle\UserBundle\PasswordChangeEnforcer\Listener;

use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Entity\PasswordChangeUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordChangeEnforcerSubscriber implements EventSubscriberInterface
{
    private $changePasswordRoute;

    private $urlGenerator;

    public static function getSubscribedEvents()
    {
        yield UserRequestInterceptionEvent::class => ['checkNeedNeedChangePassword', 100];
    }

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        string $changePasswordRoute = 'admin_change_password'
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->changePasswordRoute = $changePasswordRoute;
    }

    public function checkNeedNeedChangePassword(UserRequestInterceptionEvent $event): void
    {
        $user = $event->getUser();

        switch (true) {
            case !$user instanceof PasswordChangeUserInterface:
            case !$user->getNeedChangePassword():
                return;
        }

        $request = $event->getRequest();

        if ($request->attributes->get('_route') === $this->changePasswordRoute) {
            $event->allowHandlingRequest();

            return;
        }

        $event->setResponse(
            new RedirectResponse($this->urlGenerator->generate($this->changePasswordRoute)),
            'need_change_password'
        );
    }
}
