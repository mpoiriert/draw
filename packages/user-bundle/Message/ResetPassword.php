<?php

namespace Draw\Bundle\UserBundle\Message;

class ResetPassword extends RedirectToSecuredRouteMessage
{
    /**
     * @param mixed $userId
     */
    public function __construct($userId, string $route = 'admin_change_password')
    {
        parent::__construct($userId, $route);
    }
}
