<?php

namespace Draw\Component\Messenger\SerializerEventDispatcher\Event;

use Symfony\Component\Messenger\Envelope;

class PreEncodeEvent extends BaseSerializerEvent
{
    public function setEnvelope(Envelope $envelope): void
    {
        $this->envelope = $envelope;
    }
}
