<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Component\Messenger\Message\ManuallyTriggeredInterface;

class AutoConnect implements AutoConnectInterface, ManuallyTriggeredInterface
{
    private $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
