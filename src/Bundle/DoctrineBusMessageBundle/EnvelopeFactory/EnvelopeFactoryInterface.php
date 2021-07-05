<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory;

use Symfony\Component\Messenger\Envelope;

interface EnvelopeFactoryInterface
{
    public function createEnvelope($message): ?Envelope;
}
