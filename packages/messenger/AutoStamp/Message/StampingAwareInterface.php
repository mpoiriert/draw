<?php

namespace Draw\Component\Messenger\AutoStamp\Message;

use Symfony\Component\Messenger\Envelope;

interface StampingAwareInterface
{
    public function stamp(Envelope $envelope): Envelope;
}
