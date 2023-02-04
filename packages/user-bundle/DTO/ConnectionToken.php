<?php

namespace Draw\Bundle\UserBundle\DTO;

class ConnectionToken
{
    public function __construct(public string $token)
    {
    }
}
