<?php

namespace Draw\Component\Messenger\Exception;

use Symfony\Component\Messenger\Exception\ExceptionInterface;

class MessageNotFoundException extends \Exception implements ExceptionInterface
{
    public function __construct($messageId)
    {
        parent::__construct(sprintf('Message id [%s] not found', $messageId));
    }
}
