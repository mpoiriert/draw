<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Bundle\MessengerBundle\Message\RedirectToRouteMessageInterface;
use Draw\Bundle\MessengerBundle\Message\RedirectToRouteMessageTrait;
use Draw\Component\Messenger\Message\ManuallyTriggeredInterface;

class RedirectToSecuredRouteMessage implements ManuallyTriggeredInterface, AutoConnectInterface, RedirectToRouteMessageInterface
{
    use RedirectToRouteMessageTrait;

    private $userId;

    public function __construct($userId, string $route, array $urlParameters = [])
    {
        $this->userId = $userId;
        $this->route = $route;
        $this->urlParameters = $urlParameters;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
