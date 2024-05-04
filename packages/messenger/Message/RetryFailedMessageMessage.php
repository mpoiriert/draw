<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\Message;

class RetryFailedMessageMessage
{
    public function __construct(
        private string $messageId,
    ) {
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }
}
