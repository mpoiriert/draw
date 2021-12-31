<?php

namespace Draw\Component\Messenger\Tests\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\Messenger\Stamp\SearchableTagStamp;
use Draw\Component\Messenger\Tests\TestCase;
use Draw\Component\Messenger\Transport\DrawTransport;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;

class DrawTransportTest extends TestCase
{
    /**
     * @var DrawTransport
     */
    private $drawTransport;

    /**
     * @beforeClass
     * @afterClass
     */
    public static function cleanUp(): void
    {
        static::getService(EntityManagerInterface::class)
            ->getConnection()
            ->executeQuery('DELETE FROM draw_messenger__message');
    }

    public function setUp(): void
    {
        $this->drawTransport = static::getService(DrawTransport::class);
    }

    public function testSendMessage(): Envelope
    {
        $envelope = $this->drawTransport->send(new Envelope(new stdClass(), [new SearchableTagStamp(['tag1'])]));

        $this->assertNotNull($envelope->last(TransportMessageIdStamp::class)->getId());

        return $envelope;
    }

    /**
     * @depends testSendMessage
     */
    public function testFindByTag(Envelope $referencedEnvelope): void
    {
        $envelopes = $this->drawTransport->findByTag('tag1');

        $this->assertCount(1, $envelopes);
        $this->assertInstanceOf(Envelope::class, $envelopes[0]);

        $this->assertSame(
            $referencedEnvelope->last(TransportMessageIdStamp::class)->getId(),
            $envelopes[0]->last(TransportMessageIdStamp::class)->getId()
        );
    }
}
