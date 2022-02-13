<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory;

use Symfony\Component\Messenger\Envelope;

interface EnvelopeFactoryInterface
{
    /**
     * @return array|Envelope[]
     */
    public function createEnvelopes(object $messageHolder, array $messages): array;
}
