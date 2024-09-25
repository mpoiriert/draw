<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\Tests\Stub\Message;

use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;

interface PreSendAwareMessageInterface extends MessageHolderInterface
{
    public function preSend(MessageHolderInterface $messageHolder): void;
}
