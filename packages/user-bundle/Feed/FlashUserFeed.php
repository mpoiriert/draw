<?php

namespace Draw\Bundle\UserBundle\Feed;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FlashUserFeed implements UserFeedInterface
{
    private RequestStack $requestStack;

    private Security $security;

    private TranslatorInterface $translator;

    public function __construct(RequestStack $requestStack, Security $security, ?TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->translator = $translator;
    }

    public function addToFeed(UserInterface $user, string $type, string $message, array $parameters = []): void
    {
        $currentUser = $this->security->getUser();

        if ($currentUser && $this->security->getUser() !== $user) {
            return;
        }

        try {
            $session = $this->requestStack->getSession();
            if ($session instanceof Session) {
                $session->getFlashBag()->add(
                    $type,
                    $this->translator->trans($message, $parameters, 'DrawUserFeed')
                );
            }
        } catch (SessionNotFoundException $error) {
            return;
        }
    }
}
