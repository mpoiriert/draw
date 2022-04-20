<?php

namespace Draw\Component\Messenger\EnvelopeFactory;

use Draw\Component\Messenger\Entity\MessageHolderInterface;
use Symfony\Component\Messenger\Envelope;

interface EnvelopeFactoryInterface
{
    /**
     * @return array|Envelope[]
     */
    public function createEnvelopes(MessageHolderInterface $messageHolder, array $messages): array;
}
