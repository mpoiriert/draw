<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\Message;

use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;

interface LifeCycleAwareMessageInterface
{
    public function preSend(MessageHolderInterface $messageHolder): void;
}
