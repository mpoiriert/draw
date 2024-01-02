<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory;

use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;
use Symfony\Component\Messenger\Envelope;

interface EnvelopeFactoryInterface
{
    /**
     * @return array|Envelope[]
     */
    public function createEnvelopes(MessageHolderInterface $messageHolder, array $messages): array;
}
