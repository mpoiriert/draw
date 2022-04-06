<?php

namespace Draw\Bundle\UserBundle\Feed;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserFeedInterface
{
    public function addToFeed(UserInterface $user, string $type, string $message): void;
}
