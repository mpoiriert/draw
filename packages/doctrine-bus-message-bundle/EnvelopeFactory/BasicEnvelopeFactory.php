<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory;

use Symfony\Component\Messenger\Envelope;

class BasicEnvelopeFactory implements EnvelopeFactoryInterface
{
    public function createEnvelopes(object $object, array $messages): array
    {
        $envelopes = [];
        foreach ($messages as $message) {
            $envelopes[] = new Envelope($message);
        }

        return $envelopes;
    }
}
