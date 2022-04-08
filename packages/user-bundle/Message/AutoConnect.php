<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Component\Messenger\Message\ManuallyTriggeredInterface;
use Draw\Component\Security\Http\Message\AutoConnectInterface;

class AutoConnect implements AutoConnectInterface, ManuallyTriggeredInterface
{
    private string $userIdentifier;

    public function __construct(string $userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
