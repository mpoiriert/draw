<?php

namespace Draw\Component\Messenger\Entity;

interface DrawMessageInterface
{
    public function getMessageId(): ?string;

    public function getQueueName(): ?string;
}
