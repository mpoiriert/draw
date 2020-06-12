<?php

namespace Draw\Bundle\UserBundle\DTO;

use JMS\Serializer\Annotation as Serializer;

class ConnectionToken
{
    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    public $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }
}
