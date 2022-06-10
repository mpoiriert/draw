<?php

namespace Draw\Component\Messenger\Transport\Entity;

interface DrawMessageInterface
{
    public function getMessageId(): ?string;

    public function getQueueName(): ?string;
}
