<?php

namespace Draw\Bundle\UserBundle\EventListener;

use Draw\Bundle\UserBundle\Entity\PasswordChangeUserInterface;
use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordChangeEnforcerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            UserRequestInterceptionEvent::class => ['checkNeedNeedChangePassword', 100],
        ];
    }

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private string $changePasswordRoute = 'admin_change_password'
    ) {
    }

    public function checkNeedNeedChangePassword(UserRequestInterceptionEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof PasswordChangeUserInterface) {
            return;
        }

        if (!$user->getNeedChangePassword()) {
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
