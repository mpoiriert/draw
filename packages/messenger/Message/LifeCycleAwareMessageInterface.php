<?php

namespace Draw\Component\Messenger\Message;

use Draw\Component\Messenger\Entity\MessageHolderInterface;

interface LifeCycleAwareMessageInterface
{
    public function preSend(MessageHolderInterface $messageHolder): void;
}
