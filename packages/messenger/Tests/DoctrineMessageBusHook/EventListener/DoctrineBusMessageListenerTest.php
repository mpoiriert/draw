<?php

namespace Draw\Component\Messenger\Tests\DoctrineMessageBusHook\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Proxy;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Messenger\DoctrineMessageBusHook\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\EventListener\DoctrineBusMessageListener;
use Draw\Component\Messenger\DoctrineMessageBusHook\Message\LifeCycleAwareMessageInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;
use Draw\Component\Messenger\Tests\Stub\Message\PreSendAwareMessageInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(DoctrineBusMessageListener::class)]
class DoctrineBusMessageListenerTest extends TestCase
{
    public function testPostPersist(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $object = new DoctrineBusMessageListener(
            static::createStub(MessageBusInterface::class),
            static::createStub(EnvelopeFactoryInterface::class)
        );

        $messageHolder = static::createStub(MessageHolderInterface::class);

        $entityManager
            ->expects(static::once())
            ->method('getClassMetadata')
            ->with($messageHolder::class)
            ->willReturn($classMetadata = new ClassMetadata(uniqid()))
            ->seal()
        ;

        $classMetadata->rootEntityName = $messageHolder::class;

        $object->postPersist(
            new LifecycleEventArgs(
                $messageHolder,
                $entityManager
            )
        );

        static::assertSame(
            [$messageHolder],
            $object->getFlattenMessageHolders()
        );
    }

    public function testPostPersistNotMessageHolderEntity(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $object = new DoctrineBusMessageListener(
            static::createStub(MessageBusInterface::class),
            static::createStub(EnvelopeFactoryInterface::class)
        );

        $messageHolder = (object) [];

        $entityManager
            ->expects(static::never())
            ->method('getClassMetadata')
            ->seal()
        ;

        $object->postPersist(
            new LifecycleEventArgs(
                $messageHolder,
                $entityManager
            )
        );

        static::assertSame(
            [],
            $object->getFlattenMessageHolders()
        );
    }

    public function testPostLoad(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $object = new DoctrineBusMessageListener(
            static::createStub(MessageBusInterface::class),
            static::createStub(EnvelopeFactoryInterface::class)
        );

        $messageHolder = static::createStub(MessageHolderInterface::class);

        $entityManager
            ->expects(static::once())
            ->method('getClassMetadata')
            ->with($messageHolder::class)
            ->willReturn($classMetadata = new ClassMetadata(uniqid()))
            ->seal()
        ;

        $classMetadata->rootEntityName = $messageHolder::class;

        $object->postLoad(
            new LifecycleEventArgs(
                $messageHolder,
                $entityManager
            )
        );

        static::assertSame(
            [$messageHolder],
            $object->getFlattenMessageHolders()
        );
    }

    public function testOnClearAll(): void
    {
        $object = new DoctrineBusMessageListener(
            static::createStub(MessageBusInterface::class),
            static::createStub(EnvelopeFactoryInterface::class)
        );

        $this->addMessageHolder(
            $object,
            static::createStub(MessageHolderInterface::class)
        );

        $object->onClear(
            new OnClearEventArgs(static::createStub(EntityManagerInterface::class))
        );

        static::assertSame(
            [],
            $object->getFlattenMessageHolders()
        );
    }

    public function testOnClear(): void
    {
        $object = new DoctrineBusMessageListener(
            static::createStub(MessageBusInterface::class),
            static::createStub(EnvelopeFactoryInterface::class)
        );

        $this->addMessageHolder(
            $object,
            static::createStub(MessageHolderInterface::class)
        );

        $object->onClear(
            new OnClearEventArgs(static::createStub(EntityManagerInterface::class))
        );

        static::assertCount(
            0,
            $object->getFlattenMessageHolders()
        );
    }

    public function testPostFlushEmpty(): void
    {
        $object = new DoctrineBusMessageListener(
            $messageBus = $this->createMock(MessageBusInterface::class),
            $envelopeFactory = $this->createMock(EnvelopeFactoryInterface::class)
        );

        $envelopeFactory
            ->expects(static::never())
            ->method('createEnvelopes')
            ->seal()
        ;

        $messageBus
            ->expects(static::never())
            ->method('dispatch')
            ->seal()
        ;

        $object->postFlush();
    }

    public function testPostFlushOnlyUninitializedProxy(): void
    {
        $object = new DoctrineBusMessageListener(
            $messageBus = $this->createMock(MessageBusInterface::class),
            $envelopeFactory = $this->createMock(EnvelopeFactoryInterface::class)
        );

        $this->addMessageHolder(
            $object,
            new class implements Proxy, MessageHolderInterface {
                public function getOnHoldMessages(bool $clear): array
                {
                    return [];
                }

                public function __load(): void
                {
                }

                public function __isInitialized(): bool
                {
                    return false;
                }
            }
        );

        $envelopeFactory
            ->expects(static::never())
            ->method('createEnvelopes')
            ->seal()
        ;

        $messageBus
            ->expects(static::never())
            ->method('dispatch')
            ->seal()
        ;

        $object->postFlush();
    }

    public function testPostFlushWithOneMessage(): void
    {
        $object = new DoctrineBusMessageListener(
            $messageBus = $this->createMock(MessageBusInterface::class),
            $envelopeFactory = $this->createMock(EnvelopeFactoryInterface::class)
        );

        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($object, $messageHolder);

        $messageHolder->expects(static::once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn(
                $messages = [
                    $message1 = $this->createMock(LifeCycleAwareMessageInterface::class),
                    // Arbitrary interface just to add preSend method without implementing LifeCycleAwareMessageInterface
                    $message2 = $this->createMock(PreSendAwareMessageInterface::class),
                ]
            )
            ->seal()
        ;

        $message1
            ->expects(static::once())
            ->method('preSend')
            ->with($messageHolder)
            ->seal()
        ;

        $message2
            ->expects(static::never())
            ->method('preSend')
            ->seal()
        ;

        $envelopeFactory
            ->expects(static::once())
            ->method('createEnvelopes')
            ->with($messageHolder, $messages)
            ->willReturn([$envelope = new Envelope((object) [])])
            ->seal()
        ;

        $messageBus
            ->expects(static::once())
            ->method('dispatch')
            ->with($envelope)
            ->willReturnArgument(0)
            ->seal()
        ;

        $object->postFlush();
    }

    public function testPostFlushWithMultipleMessageHolder(): void
    {
        $object = new DoctrineBusMessageListener(
            $messageBus = $this->createMock(MessageBusInterface::class),
            $envelopeFactory = $this->createMock(EnvelopeFactoryInterface::class)
        );

        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($object, $messageHolder);

        $messageHolder
            ->expects(static::once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn([(object) []])
            ->seal()
        ;

        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($object, $messageHolder);

        $messageHolder
            ->expects(static::once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn([(object) []])
            ->seal()
        ;

        $envelopeFactory
            ->expects(static::exactly(2))
            ->method('createEnvelopes')
            ->willReturn([$envelope = new Envelope((object) [])])
            ->seal()
        ;

        $messageBus
            ->expects(static::exactly(2))
            ->method('dispatch')
            ->with($envelope)
            ->willReturnArgument(0)
            ->seal()
        ;

        $object->postFlush();
    }

    public function testReset(): void
    {
        $object = new DoctrineBusMessageListener(
            static::createStub(MessageBusInterface::class),
            static::createStub(EnvelopeFactoryInterface::class)
        );

        $messageHolder = static::createStub(MessageHolderInterface::class);

        $this->addMessageHolder($object, $messageHolder);

        static::assertSame(
            [$messageHolder],
            $object->getFlattenMessageHolders()
        );

        $object->reset();

        static::assertSame(
            [],
            $object->getFlattenMessageHolders()
        );
    }

    private function addMessageHolder(DoctrineBusMessageListener $object, MessageHolderInterface $messageHolder): void
    {
        $messageHolders = ReflectionAccessor::getPropertyValue($object, 'messageHolders');
        $messageHolders[$messageHolder::class][spl_object_id($messageHolder)] = $messageHolder;

        ReflectionAccessor::setPropertyValue(
            $object,
            'messageHolders',
            $messageHolders
        );
    }
}
