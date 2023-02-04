<?php

namespace Draw\Component\Messenger\SerializerEventDispatcher\Event;

use Symfony\Component\Messenger\Envelope;

abstract class BaseSerializerEvent
{
    public function __construct(protected Envelope $envelope)
    {
    }

    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }
}
