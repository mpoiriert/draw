<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory;

use Symfony\Component\Messenger\Envelope;

class BasicEnvelopeFactory implements EnvelopeFactoryInterface
{
    public function createEnvelope($message): ?Envelope
    {
        return new Envelope($message);
    }
}
