<?php

namespace Draw\Component\Messenger\Tests\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Proxy;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Messenger\Entity\MessageHolderInterface;
use Draw\Component\Messenger\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Component\Messenger\EventListener\DoctrineBusMessageListener;
use Draw\Component\Messenger\Message\LifeCycleAwareMessageInterface;
use Draw\Component\Tester\MockBuilderTrait;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @covers \Draw\Component\Messenger\EventListener\DoctrineBusMessageListener
 */
class DoctrineBusMessageListenerTest extends TestCase
{
    use MockBuilderTrait;

    private DoctrineBusMessageListener $service;

    private EnvelopeFactoryInterface $envelopeFactory;

    private MessageBusInterface $messageBus;

    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->service = new DoctrineBusMessageListener(
            $this->messageBus = $this->createMock(MessageBusInterface::class),
            $this->envelopeFactory = $this->createMock(EnvelopeFactoryInterface::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriber::class,
            $this->service
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                Events::postPersist,
                Events::postLoad,
                Events::postFlush,
                Events::onClear,
            ],
            $this->service->getSubscribedEvents()
        );
    }

    public function testPostPersist(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($messageHolder))
            ->willReturn($classMetadata = new ClassMetadata(uniqid()));

        $classMetadata->rootEntityName = get_class($messageHolder);

        $this->service->postPersist(
            new LifecycleEventArgs(
                $messageHolder,
                $this->entityManager
            )
        );

        $this->assertSame(
            [$messageHolder],
            $this->service->getFlattenMessageHolders()
        );
    }

    public function testPostPersistNotMessageHolderEntity(): void
    {
        $messageHolder = (object) [];

        $this->entityManager
            ->expects($this->never())
            ->method('getClassMetadata');

        $this->service->postPersist(
            new LifecycleEventArgs(
                $messageHolder,
                $this->entityManager
            )
        );

        $this->assertSame(
            [],
            $this->service->getFlattenMessageHolders()
        );
    }

    public function testPostLoad(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($messageHolder))
            ->willReturn($classMetadata = new ClassMetadata(uniqid()));

        $classMetadata->rootEntityName = get_class($messageHolder);

        $this->service->postLoad(
            new LifecycleEventArgs(
                $messageHolder,
                $this->entityManager
            )
        );

        $this->assertSame(
            [$messageHolder],
            $this->service->getFlattenMessageHolders()
        );
    }

    public function testOnClearAll(): void
    {
        $this->addMessageHolder(
            $this->createMock(MessageHolderInterface::class)
        );

        $this->service->onClear(new OnClearEventArgs($this->entityManager));

        $this->assertSame(
            [],
            $this->service->getFlattenMessageHolders()
        );
    }

    public function testOnClearSpecific(): void
    {
        $this->addMessageHolder(
            $messageHolder = $this->createMock(MessageHolderInterface::class)
        );

        $this->service->onClear(new OnClearEventArgs($this->entityManager, get_class($messageHolder)));

        $this->assertCount(
            0,
            $this->service->getFlattenMessageHolders()
        );
    }

    public function testOnClearOther(): void
    {
        $this->addMessageHolder(
            $this->createMock(MessageHolderInterface::class)
        );

        $this->service->onClear(new OnClearEventArgs($this->entityManager, stdClass::class));

        $this->assertCount(
            1,
            $this->service->getFlattenMessageHolders()
        );
    }

    public function testPostFlushEmpty(): void
    {
        $this->envelopeFactory
            ->expects($this->never())
            ->method('createEnvelopes');

        $this->messageBus
            ->expects($this->never())
            ->method('dispatch');

        $this->service->postFlush();
    }

    public function testPostFlushOnlyUninitializedProxy(): void
    {
        $this->addMessageHolder(
            $messageHolder = $this->createMock(Proxy::class)
        );

        $messageHolder
            ->expects($this->once())
            ->method('__isInitialized')
            ->willReturn(false);

        $this->envelopeFactory
            ->expects($this->never())
            ->method('createEnvelopes');

        $this->messageBus
            ->expects($this->never())
            ->method('dispatch');

        $this->service->postFlush();
    }

    public function testPostFlushWithOneMessage(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        $messageHolder->expects($this->once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn(
                $messages = [
                    $message1 = $this->createMock(LifeCycleAwareMessageInterface::class),
                    // Arbitrary interface just to add preSen method without implementing LifeCycleAwareMessageInterface
                    $message2 = $this->createMockWithExtraMethods(MessageHolderInterface::class, ['preSend']),
                ]
            );

        $message1
            ->expects($this->once())
            ->method('preSend')
            ->with($messageHolder);

        $message2
            ->expects($this->never())
            ->method('preSend');

        $this->envelopeFactory
            ->expects($this->once())
            ->method('createEnvelopes')
            ->with($messageHolder, $messages)
            ->willReturn([$envelope = new Envelope((object) [])]);

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($envelope)
            ->willReturnArgument(0);

        $this->service->postFlush();
    }

    public function testPostFlushWithMultipleMessageHolder(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        $messageHolder
            ->expects($this->once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn([(object) []]);

        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        $messageHolder
            ->expects($this->once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn([(object) []]);

        $this->envelopeFactory
            ->expects($this->exactly(2))
            ->method('createEnvelopes')
            ->willReturn([$envelope = new Envelope((object) [])]);

        $this->messageBus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with($envelope)
            ->willReturnArgument(0);

        $this->service->postFlush();
    }

    private function addMessageHolder($messageHolder): void
    {
        $messageHolders = ReflectionAccessor::getPropertyValue($this->service, 'messageHolders');
        $messageHolders[get_class($messageHolder)][spl_object_id($messageHolder)] = $messageHolder;

        ReflectionAccessor::setPropertyValue(
           $this->service,
           'messageHolders',
           $messageHolders
       );
    }
}
