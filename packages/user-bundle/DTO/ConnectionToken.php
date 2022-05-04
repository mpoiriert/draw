<?php

namespace Draw\Bundle\UserBundle\DTO;

class ConnectionToken
{
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }
}
