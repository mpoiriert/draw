<?php

namespace Draw\Component\Messenger\Tests\Transport;

use Draw\Component\Messenger\Stamp\SearchableTagStamp;
use Draw\Component\Messenger\Tests\TestCase;
use Draw\Component\Messenger\Transport\DrawTransport;
use Symfony\Component\Messenger\Envelope;

class DrawTransportTest extends TestCase
{
    public function testSendMessage(): void
    {
        $transport = static::getService(DrawTransport::class);

        $envelope = new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1'])]);
        $transport->send($envelope);
    }
}
