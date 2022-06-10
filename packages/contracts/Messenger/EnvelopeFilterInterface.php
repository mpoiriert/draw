<?php

namespace Draw\Contracts\Messenger;

use Symfony\Component\Messenger\Envelope;

interface EnvelopeFilterInterface
{
    /**
     * @return bool True if Envelope is valid, false otherwise
     */
    public function __invoke(Envelope $envelope): bool;
}
