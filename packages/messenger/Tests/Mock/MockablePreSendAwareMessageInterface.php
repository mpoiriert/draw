<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\Tests\Mock;

use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;

interface MockablePreSendAwareMessageInterface extends MessageHolderInterface
{
    public function preSend(MessageHolderInterface $messageHolder): void;
}
