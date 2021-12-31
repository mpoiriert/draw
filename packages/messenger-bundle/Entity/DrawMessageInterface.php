<?php

namespace Draw\Bundle\MessengerBundle\Entity;

interface DrawMessageInterface
{
    public function getMessageId(): ?string;

    public function getQueueName(): ?string;
}
