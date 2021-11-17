<?php

namespace Draw\Bundle\UserBundle\Feed;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SessionUserFeed implements UserFeedInterface
{
    /**
     * @var SessionInterface|Session
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function addToFeed(UserInterface $user, string $type, string $message)
    {
        $this->session->getFlashBag()->add($type, $message);
    }
}
