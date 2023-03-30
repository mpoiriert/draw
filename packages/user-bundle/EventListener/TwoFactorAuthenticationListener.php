<?php

namespace Draw\Bundle\UserBundle\EventListener;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ByTimeBaseOneTimePasswordInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class TwoFactorAuthenticationListener implements EventSubscriberInterface
{
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
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
        private string $enableRoute = 'admin_app_user_enable-2fa',
        private array $allowedRoutes = ['admin_app_user_disable-2fa'],
    ) {
    }

    public function checkNeedToEnableTwoFactorAuthentication(UserRequestInterceptionEvent $event): void
    {
        $user = $event->getUser();
        $request = $event->getRequest();

        if (!$user instanceof SecurityUserInterface) {
            return;
        }

        if (!$user instanceof ByTimeBaseOneTimePasswordInterface) {
            return;
        }

        if ($user->isTotpAuthenticationEnabled()) {
            return;
        }

        if (!$user->isForceEnablingTwoFactorAuthentication() && !$user->needToEnableTotpAuthenticationEnabled()) {
            return;
        }

        if (\in_array($request->attributes->get('_route'), [...$this->allowedRoutes, $this->enableRoute], true)) {
            $event->allowHandlingRequest();

            return;
        }

        $event->setResponse(
            new RedirectResponse($this->urlGenerator->generate($this->enableRoute, ['id' => $user->getId()])),
            '2fa_need_enabling'
        );
    }

    public function allowHandlingRequestWhenTwoFactorAuthenticationInProgress(UserRequestInterceptionEvent $event): void
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_2FA_IN_PROGRESS')) {
            $event->allowHandlingRequest();
        }
    }
}
