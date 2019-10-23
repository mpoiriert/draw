<?php namespace Draw\Bundle\UserBundle\Message;

use Draw\Bundle\MessengerBundle\Message\RedirectToRouteMessageInterface;
use Draw\Bundle\MessengerBundle\Message\RedirectToRouteMessageTrait;
use Draw\Component\Messenger\Message\ManuallyTriggeredInterface;

class ResetPassword implements ManuallyTriggeredInterface, AutoConnectInterface, RedirectToRouteMessageInterface
{
    use RedirectToRouteMessageTrait;

    private $userId;

    public function __construct($userId, $route = 'admin_change_password')
    {
        $this->route = $route;
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}