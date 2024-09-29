<?php

namespace Draw\Component\Messenger\Tests\Transport;

use Doctrine\DBAL\Connection;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\DoctrineTransaction\NoTransaction;
use Draw\Component\Messenger\Expirable\PurgeableTransportInterface;
use Draw\Component\Messenger\Expirable\Stamp\ExpirationStamp;
use Draw\Component\Messenger\Searchable\SearchableTransportInterface;
use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use Draw\Component\Messenger\Tests\TestCase;
use Draw\Component\Messenger\Transport\DrawTransport;
use Draw\Component\Messenger\Transport\DrawTransportFactory;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @internal
 */
#[CoversClass(DrawTransport::class)]
#[NoTransaction]
class DrawTransportTest extends TestCase
{
    use MockTrait;

    private DrawTransport $service;

    #[
        BeforeClass,
        AfterClass
    ]
    public static function cleanUp(): void
    {
        try {
            static::loadDefaultConnection()
                ->executeStatement('DELETE FROM draw_messenger__message')
            ;
        } catch (\Throwable) {
            // Table may not exist we ignore it
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
            ->executeStatement('DROP TABLE IF EXISTS draw_messenger__message_tag, draw_messenger__message')
        ;

        $this->service->setup();
        static::assertTrue(true);
    }

    #[Depends('testSetup')]
    public function testSendException(): void
    {
        $driverConnection = $this->mockProperty(
            $this->service,
            'driverConnection',
            Connection::class
        );

        $driverConnection->expects(static::once())
            ->method('createQueryBuilder')
            ->willThrowException(new \Exception($exceptionMessage = uniqid('exception-message-')))
        ;

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->service->send(new Envelope(new \stdClass()));
    }

    #[Depends('testSetup')]
    public function testSendNegativeDelay(): void
    {
        $envelope = $this->service->send(new Envelope(
            new \stdClass(),
            [new DelayStamp(-10000)]
        ));

        $id = $envelope->last(TransportMessageIdStamp::class)->getId();

        static::assertNotNull($id);

        $result = static::loadDefaultConnection()
            ->executeQuery(
                'SELECT available_at FROM draw_messenger__message WHERE id = ?',
                [
                    $id,
                ]
            )->fetchAllAssociative()
        ;

        static::assertEqualsWithDelta(
            strtotime('now - 10 seconds'),
            strtotime($result[0]['available_at']),
            1
        );
    }

    #[Depends('testSetup')]
    public function testSend(): Envelope
    {
        $envelope = $this->service->send(new Envelope(
            new \stdClass(),
            [new SearchableTagStamp(['tag1', 'tag2'])]
        ));

        static::assertNotNull($envelope->last(TransportMessageIdStamp::class)->getId());

        return $envelope;
    }

    #[Depends('testSend')]
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

    #[Depends('testSend')]
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

    #[Depends('testSetup')]
    public function testFindByTagsNoTags(): void
    {
        $driverConnection = $this->mockProperty(
            $this->service,
            'driverConnection',
            Connection::class
        );

        $driverConnection->expects(static::never())
            ->method('createQueryBuilder')
        ;

        static::assertEmpty($this->service->findByTags([]));
    }

    #[Depends('testSend')]
    public function testFindByTagsNotMatch(): void
    {
        static::assertCount(0, $this->service->findByTags(['tag3']));
    }

    #[Depends('testSend')]
    public function testFindAfterAcknowledge(Envelope $referencedEnvelope): void
    {
        $foundEnvelope = $this->service->find($referencedEnvelope->last(TransportMessageIdStamp::class)->getId());
        static::assertNotNull($foundEnvelope);

        $this->service->ack($foundEnvelope);

        static::assertNull($this->service->find($foundEnvelope->last(TransportMessageIdStamp::class)->getId()));
    }

    public static function provideTestSendSearchableMessage(): iterable
    {
        yield 'uniqueness' => [
            [
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1'], true)]),
            ],
            ['tag1'],
            1,
        ];

        yield 'uniqueness-one-tag-against-multiple' => [
            [
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1'], true)]),
            ],
            ['tag1'],
            1,
        ];

        yield 'uniqueness-other-not-affected' => [
            [
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag2'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag2'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1'], true)]),
            ],
            ['tag2'],
            2,
        ];

        yield 'uniqueness-multiple-tags' => [
            [
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1', 'tag2'], true)]),
            ],
            ['tag2'],
            1,
        ];

        yield 'uniqueness-multiple-tags-no-match' => [
            [
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag2', 'tag3'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1', 'tag2'], true)]),
            ],
            ['tag2'],
            2,
        ];

        yield 'reinsertion-does-not-clean' => [
            [
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new \stdClass(), [new RedeliveryStamp(1), new SearchableTagStamp(['tag1', 'tag2'], true)]),
            ],
            ['tag2'],
            2,
        ];

        yield 'no-tag' => [
            [
                new Envelope(new \stdClass(), [new SearchableTagStamp(['tag1', 'tag2'])]),
                new Envelope(new \stdClass(), [new SearchableTagStamp([], true)]),
            ],
            ['tag2'],
            1,
        ];
    }

    /**
     * @param array|Envelope[] $insertEnvelopes
     * @param array|string[]   $searchTags
     */
    #[
        Depends('testSetup'),
        DataProvider('provideTestSendSearchableMessage')
    ]
    public function testSendSearchableMessage(array $insertEnvelopes, array $searchTags, int $resultCount): void
    {
        static::cleanUp();

        foreach ($insertEnvelopes as $envelope) {
            $this->service->send($envelope);
        }

        static::assertCount($resultCount, $this->service->findByTags($searchTags));
    }

    #[Depends('testSetup')]
    public function testPurgeObsoleteMessages(): void
    {
        static::cleanUp();

        $this->service->send(
            new Envelope(
                new \stdClass(),
                [
                    new ExpirationStamp(new \DateTimeImmutable('- 5 minutes')),
                    new SearchableTagStamp($tags = ['tag1']),
                ]
            )
        );

        static::assertCount(1, $this->service->findByTags($tags));

        $this->service->purgeObsoleteMessages(new \DateTimeImmutable());

        static::assertCount(0, $this->service->findByTags($tags));
    }
}
