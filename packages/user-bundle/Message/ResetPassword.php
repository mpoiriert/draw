<?php

namespace Draw\Bundle\UserBundle\Message;

class ResetPassword extends RedirectToSecuredRouteMessage
{
    public function __construct($userId, $route = 'admin_change_password')
    {
        parent::__construct($userId, $route);
    }
}
