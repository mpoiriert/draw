<?php

namespace Draw\Component\Messenger\EnvelopeFactory;

use Draw\Component\Messenger\Entity\MessageHolderInterface;
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
