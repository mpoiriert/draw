<?php

namespace Draw\Bundle\UserBundle\Feed;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserFeedInterface
{
    /**
     * @phpstan-param array<string,mixed> $parameters
     */
    public function addToFeed(UserInterface $user, string $type, string $message, array $parameters = [], string $domain = 'DrawUserFeed'): void;
}
