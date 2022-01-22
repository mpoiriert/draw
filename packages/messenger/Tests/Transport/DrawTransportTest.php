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
        $envelope = $this->drawTransport->send(new Envelope(new stdClass(),
            [new SearchableTagStamp(['tag1', 'tag2'])]));

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

    /**
     * @depends testSendMessage
     */
    public function testFindByTags(Envelope $referencedEnvelope): void
    {
        $envelopes = $this->drawTransport->findByTags(['tag1', 'tag2']);

        $this->assertCount(1, $envelopes);
        $this->assertInstanceOf(Envelope::class, $envelopes[0]);

        $this->assertSame(
            $referencedEnvelope->last(TransportMessageIdStamp::class)->getId(),
            $envelopes[0]->last(TransportMessageIdStamp::class)->getId()
        );
    }

    /**
     * @depends testSendMessage
     */
    public function testFindByTagsNotMatch(): void
    {
        $this->assertCount(0, $this->drawTransport->findByTags(['tag3']));
    }

    public function provideTestSendSearchableMessage(): iterable
    {
        yield 'uniqueness' => [
            [
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1'], true)]),
            ],
            ['tag1'],
            1,
        ];

        yield 'uniqueness-one-tag-against-multiple' => [
            [
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1'], true)]),
            ],
            ['tag1'],
            1,
        ];

        yield 'uniqueness-other-not-affected' => [
            [
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag2'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag2'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1'], true)]),
            ],
            ['tag2'],
            2,
        ];

        yield 'uniqueness-multiple-tags' => [
            [
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1', 'tag2'], true)]),
            ],
            ['tag2'],
            1,
        ];

        yield 'uniqueness-multiple-tags-no-match' => [
            [
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag2', 'tag3'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1', 'tag2'], true)]),
            ],
            ['tag2'],
            2,
        ];
    }

    /**
     * @dataProvider provideTestSendSearchableMessage
     *
     * @param array|Envelope[] $insertEnvelopes
     * @param array|string[]   $searchTags
     */
    public function testSendSearchableMessage(array $insertEnvelopes, array $searchTags, int $resultCount): void
    {
        static::cleanUp();

        foreach ($insertEnvelopes as $envelope) {
            $this->drawTransport->send($envelope);
        }

        $this->assertCount($resultCount, $this->drawTransport->findByTags($searchTags));
    }
}
