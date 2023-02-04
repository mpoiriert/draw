<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Component\Messenger\ManualTrigger\Message\ManuallyTriggeredInterface;
use Draw\Component\Security\Http\Message\AutoConnectInterface;

class AutoConnect implements AutoConnectInterface, ManuallyTriggeredInterface
{
    public function __construct(private string $userIdentifier)
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
