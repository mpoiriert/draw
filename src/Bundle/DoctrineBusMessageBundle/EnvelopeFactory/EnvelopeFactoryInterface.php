<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory;

use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface;
use Symfony\Component\Messenger\Envelope;

interface EnvelopeFactoryInterface
{
    /**
     * @return array|Envelope[]
     */
    public function createEnvelopes(MessageHolderInterface $object, array $messages): array;
}
