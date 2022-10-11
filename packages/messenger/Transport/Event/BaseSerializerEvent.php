<?php

namespace Draw\Component\Messenger\Transport\Event;

use Symfony\Component\Messenger\Envelope;

abstract class BaseSerializerEvent
{
    protected Envelope $envelope;

    public function __construct(Envelope $envelope)
    {
        $this->envelope = $envelope;
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }
}
