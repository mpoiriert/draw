<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory;

use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface;
use Symfony\Component\Messenger\Envelope;

class BasicEnvelopeFactory implements EnvelopeFactoryInterface
{
    public function createEnvelopes(MessageHolderInterface $object, array $messages): array
    {
        $envelopes = [];
        foreach ($messages as $message) {
            $envelopes[] = new Envelope($message);
        }

        return $envelopes;
    }
}
