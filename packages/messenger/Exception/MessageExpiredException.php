<?php

namespace Draw\Component\Messenger\Exception;

use DateTimeInterface;
use Exception;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

class MessageExpiredException extends Exception implements ExceptionInterface
{
    private DateTimeInterface $expiredAt;

    public function __construct($messageId, DateTimeInterface $expiredAt)
    {
        $this->expiredAt = $expiredAt;
        parent::__construct(
            sprintf(
                'Message id [%s] expired on [%s]', $messageId, $this->expiredAt->format('c')
            )
        );
    }

    public function getExpiredAt(): DateTimeInterface
    {
        return $this->expiredAt;
    }
}
