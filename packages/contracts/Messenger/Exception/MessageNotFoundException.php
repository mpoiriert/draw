<?php

namespace Draw\Contracts\Messenger\Exception;

use Exception;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

class MessageNotFoundException extends Exception implements ExceptionInterface
{
    public function __construct(string $messageId)
    {
        parent::__construct(sprintf('Message id [%s] not found', $messageId));
    }
}
