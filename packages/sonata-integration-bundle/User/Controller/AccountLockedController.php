<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class AccountLockedController
{
    /**
     * @Route(name="draw_user_account_locker_account_locked", path="/account-locked")
     */
    public function indexAction(Environment $twig): Response
    {
        return new Response(
            $twig->render('@DrawSonataIntegration/UserLock/account_locked.html.twig')
        );
    }
}
