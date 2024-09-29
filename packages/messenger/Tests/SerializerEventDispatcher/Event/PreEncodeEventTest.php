<?php

namespace Draw\Component\Messenger\Tests\SerializerEventDispatcher\Event;

use Draw\Component\Messenger\SerializerEventDispatcher\Event\PreEncodeEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

/**
 * @internal
 */
class PreEncodeEventTest extends TestCase
{
    private PreEncodeEvent $object;

    private Envelope $envelope;

    protected function setUp(): void
    {
        $this->object = new PreEncodeEvent(
            $this->envelope = new Envelope((object) [])
        );
    }

    public function testMutator(): void
    {
        static::assertSame(
            $this->envelope,
            $this->object->getEnvelope()
        );

        $this->object->setEnvelope($value = new Envelope((object) []));

        static::assertSame(
            $value,
            $this->object->getEnvelope()
        );
    }
}
