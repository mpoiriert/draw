<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Bundle\MessengerBundle\Message\RedirectToRouteMessageInterface;
use Draw\Bundle\MessengerBundle\Message\RedirectToRouteMessageTrait;

class RedirectToSecuredRouteMessage extends AutoConnect implements RedirectToRouteMessageInterface
{
    use RedirectToRouteMessageTrait;

    public function __construct($userId, string $route, array $urlParameters = [])
    {
        parent::__construct($userId);
        $this->route = $route;
        $this->urlParameters = $urlParameters;
    }
}
