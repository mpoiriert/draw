<?php

namespace Draw\Bundle\UserBundle\Message;

use Draw\Component\Messenger\ManualTrigger\Message\RedirectToRouteMessageInterface;
use Draw\Component\Messenger\ManualTrigger\Message\RedirectToRouteMessageTrait;

class RedirectToSecuredRouteMessage extends AutoConnect implements RedirectToRouteMessageInterface
{
    use RedirectToRouteMessageTrait;

    /**
     * @param array<string,mixed> $urlParameters
     */
    public function __construct($userId, string $route, array $urlParameters = [])
    {
        parent::__construct($userId);
        $this->route = $route;
        $this->urlParameters = $urlParameters;
    }
}
