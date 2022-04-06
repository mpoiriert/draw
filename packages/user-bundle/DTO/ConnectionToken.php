<?php

namespace Draw\Bundle\UserBundle\DTO;

use JMS\Serializer\Annotation as Serializer;

class ConnectionToken
{
    /**
     * @Serializer\Type("string")
     */
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }
}
