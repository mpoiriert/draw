<?php

namespace Draw\Component\Messenger\Tests\Transport;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Messenger\Expirable\PurgeableTransportInterface;
use Draw\Component\Messenger\Expirable\Stamp\ExpirationStamp;
use Draw\Component\Messenger\Searchable\SearchableTransportInterface;
use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use Draw\Component\Messenger\Tests\TestCase;
use Draw\Component\Messenger\Transport\DrawTransport;
use Draw\Component\Messenger\Transport\DrawTransportFactory;
use Exception;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Throwable;

/**
 * @covers \Draw\Component\Messenger\Transport\DrawTransport
 */
class DrawTransportTest extends TestCase
{
    private DrawTransport $service;

    /**
     * @beforeClass
     * @afterClass
     */
    public static function cleanUp(): void
    {
        try {
            static::loadDefaultConnection()
                ->executeStatement('DELETE FROM draw_messenger__message');
        } catch (Throwable $throwable) {
            // Table may not exists we ignore it
        }
    }

    protected function setUp(): void
    {
        $this->service = (new DrawTransportFactory($this))->createTransport(
            'draw://default',
            [],
            new PhpSerializer()
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            TransportInterface::class,
            $this->service
        );

        static::assertInstanceOf(
            PurgeableTransportInterface::class,
            $this->service
        );

        static::assertInstanceOf(
            SearchableTransportInterface::class,
            $this->service
        );

        static::assertInstanceOf(
            SetupableTransportInterface::class,
            $this->service
        );

        static::assertInstanceOf(
            MessageCountAwareInterface::class,
            $this->service
        );

        static::assertInstanceOf(
            ListableReceiverInterface::class,
            $this->service
        );
    }

    public function testSetup(): void
    {
        static::loadDefaultConnection()
            ->executeStatement('DROP TABLE IF EXISTS draw_messenger__message_tag, draw_messenger__message');

        $this->service->setup();
        static::assertTrue(true);
    }

    /**
     * @depends testSetup
     */
    public function testSendException(): void
    {
        ReflectionAccessor::setPropertyValue(
            $this->service,
            'driverConnection',
            $driverConnection = $this->createMock(Connection::class)
        );

        $driverConnection->expects(static::once())
            ->method('createQueryBuilder')
            ->willThrowException(new Exception($exceptionMessage = uniqid('exception-message-')));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->service->send(new Envelope(new stdClass()));
    }

    /**
     * @depends testSetup
     */
    public function testSend(): Envelope
    {
        $envelope = $this->service->send(new Envelope(new stdClass(),
            [new SearchableTagStamp(['tag1', 'tag2'])]));

        static::assertNotNull($envelope->last(TransportMessageIdStamp::class)->getId());

        return $envelope;
    }

    /**
     * @depends testSend
     */
    public function testFindByTag(Envelope $referencedEnvelope): void
    {
        $envelopes = $this->service->findByTag('tag1');

        static::assertCount(1, $envelopes);
        static::assertInstanceOf(Envelope::class, $envelopes[0]);

        static::assertSame(
            $referencedEnvelope->last(TransportMessageIdStamp::class)->getId(),
            $envelopes[0]->last(TransportMessageIdStamp::class)->getId()
        );
    }

    /**
     * @depends testSend
     */
    public function testFindByTags(Envelope $referencedEnvelope): void
    {
        $envelopes = $this->service->findByTags(['tag1', 'tag2']);

        static::assertCount(1, $envelopes);
        static::assertInstanceOf(Envelope::class, $envelopes[0]);

        static::assertSame(
            $referencedEnvelope->last(TransportMessageIdStamp::class)->getId(),
            $envelopes[0]->last(TransportMessageIdStamp::class)->getId()
        );
    }

    /**
     * @depends testSetup
     */
    public function testFindByTagsNoTags(): void
    {
        ReflectionAccessor::setPropertyValue(
            $this->service,
            'driverConnection',
            $driverConnection = $this->createMock(Connection::class)
        );

        $driverConnection->expects(static::never())
            ->method('createQueryBuilder');

        static::assertEmpty($this->service->findByTags([]));
    }

    /**
     * @depends testSend
     */
    public function testFindByTagsNotMatch(): void
    {
        static::assertCount(0, $this->service->findByTags(['tag3']));
    }

    /**
     * @depends testSend
     */
    public function testFindAfterAcknowledge(Envelope $referencedEnvelope): void
    {
        $foundEnvelope = $this->service->find($referencedEnvelope->last(TransportMessageIdStamp::class)->getId());
        static::assertNotNull($foundEnvelope);

        $this->service->ack($foundEnvelope);

        static::assertNull($this->service->find($foundEnvelope->last(TransportMessageIdStamp::class)->getId()));
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

        yield 'reinsertion-does-not-clean' => [
            [
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new stdClass(), [new RedeliveryStamp(1), new SearchableTagStamp(['tag1', 'tag2'], true)]),
            ],
            ['tag2'],
            2,
        ];

        yield 'no-tag' => [
            [
                new Envelope(new stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new stdClass(), [new SearchableTagStamp([], true)]),
            ],
            ['tag2'],
            1,
        ];
    }

    /**
     * @depends      testSetup
     * @dataProvider provideTestSendSearchableMessage
     *
     * @param array|Envelope[] $insertEnvelopes
     * @param array|string[]   $searchTags
     */
    public function testSendSearchableMessage(array $insertEnvelopes, array $searchTags, int $resultCount): void
    {
        static::cleanUp();

        foreach ($insertEnvelopes as $envelope) {
            $this->service->send($envelope);
        }

        static::assertCount($resultCount, $this->service->findByTags($searchTags));
    }

    /**
     * @depends testSetup
     */
    public function testPurgeObsoleteMessages(): void
    {
        static::cleanUp();

        $this->service->send(
            new Envelope(
                new stdClass(),
                [
                    new ExpirationStamp(new DateTimeImmutable('- 5 minutes')),
                    new SearchableTagStamp($tags = ['tag1']),
                ]
            )
        );

        static::assertCount(1, $this->service->findByTags($tags));

        $this->service->purgeObsoleteMessages(new DateTimeImmutable());

        static::assertCount(0, $this->service->findByTags($tags));
    }
}
