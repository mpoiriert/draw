<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Listener;

use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwoFactorAuthenticationSubscriber implements EventSubscriberInterface
{
    private $enableRoute;

    private $urlGenerator;

    public static function getSubscribedEvents()
    {
        yield UserRequestInterceptionEvent::class => 'checkNeedToEnableTwoFactorAuthentication';
    }

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        string $enableRoute = 'admin_app_user_enable-2fa'
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->enableRoute = $enableRoute;
    }

    public function checkNeedToEnableTwoFactorAuthentication(UserRequestInterceptionEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof TwoFactorAuthenticationUserInterface) {
            return;
        }

        if (!$user->isForceEnablingTwoFactorAuthentication() || $user->isTotpAuthenticationEnabled()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->attributes->get('_route') === $this->enableRoute) {
            $event->allowHandlingRequest();

            return;
        }

        $event->setResponse(
            new RedirectResponse($this->urlGenerator->generate($this->enableRoute, ['id' => $user->getId()])),
            '2fa_need_enabling'
        );
    }
}
