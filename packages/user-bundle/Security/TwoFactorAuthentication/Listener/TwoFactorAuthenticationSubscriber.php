<?php

namespace Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Listener;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class TwoFactorAuthenticationSubscriber implements EventSubscriberInterface
{
    private string $enableRoute;

    private Security $security;

    private UrlGeneratorInterface $urlGenerator;

    public static function getSubscribedEvents(): array
    {
        return [
            UserRequestInterceptionEvent::class => [
                ['checkNeedToEnableTwoFactorAuthentication', 50],
                ['allowHandlingRequestWhenTwoFactorAuthenticationInProgress', 1000],
            ],
        ];
    }

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Security $security,
        string $enableRoute = 'admin_app_user_enable-2fa'
    ) {
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
        $this->enableRoute = $enableRoute;
    }

    public function checkNeedToEnableTwoFactorAuthentication(UserRequestInterceptionEvent $event): void
    {
        $user = $event->getUser();
        $request = $event->getRequest();

        switch (true) {
            case !$user instanceof TwoFactorAuthenticationUserInterface:
            case !$user instanceof SecurityUserInterface:
            case !$user->isForceEnablingTwoFactorAuthentication():
            case $user->isTotpAuthenticationEnabled():
                return;
            case $request->attributes->get('_route') === $this->enableRoute:
                $event->allowHandlingRequest();

                return;
            default:
                $event->setResponse(
                    new RedirectResponse($this->urlGenerator->generate($this->enableRoute, ['id' => $user->getId()])),
                    '2fa_need_enabling'
                );
                break;
        }
    }

    public function allowHandlingRequestWhenTwoFactorAuthenticationInProgress(UserRequestInterceptionEvent $event): void
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_2FA_IN_PROGRESS')) {
            $event->allowHandlingRequest();
        }
    }
}
