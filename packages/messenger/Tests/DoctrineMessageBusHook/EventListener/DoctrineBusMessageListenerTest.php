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
use Draw\Component\Messenger\Tests\Mock\MockablePreSendAwareMessageInterface;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ResetInterface;

#[CoversClass(DoctrineBusMessageListener::class)]
class DoctrineBusMessageListenerTest extends TestCase
{
    use MockTrait;

    private DoctrineBusMessageListener $object;

    private EnvelopeFactoryInterface&MockObject $envelopeFactory;

    private MessageBusInterface&MockObject $messageBus;

    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->object = new DoctrineBusMessageListener(
            $this->messageBus = $this->createMock(MessageBusInterface::class),
            $this->envelopeFactory = $this->createMock(EnvelopeFactoryInterface::class)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ResetInterface::class,
            $this->object
        );
    }

    public function testPostPersist(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->entityManager
            ->expects(static::once())
            ->method('getClassMetadata')
            ->with($messageHolder::class)
            ->willReturn($classMetadata = new ClassMetadata(uniqid()));

        $classMetadata->rootEntityName = $messageHolder::class;

        $this->object->postPersist(
            new LifecycleEventArgs(
                $messageHolder,
                $this->entityManager
            )
        );

        static::assertSame(
            [$messageHolder],
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testPostPersistNotMessageHolderEntity(): void
    {
        $messageHolder = (object) [];

        $this->entityManager
            ->expects(static::never())
            ->method('getClassMetadata');

        $this->object->postPersist(
            new LifecycleEventArgs(
                $messageHolder,
                $this->entityManager
            )
        );

        static::assertSame(
            [],
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testPostLoad(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->entityManager
            ->expects(static::once())
            ->method('getClassMetadata')
            ->with($messageHolder::class)
            ->willReturn($classMetadata = new ClassMetadata(uniqid()));

        $classMetadata->rootEntityName = $messageHolder::class;

        $this->object->postLoad(
            new LifecycleEventArgs(
                $messageHolder,
                $this->entityManager
            )
        );

        static::assertSame(
            [$messageHolder],
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testOnClearAll(): void
    {
        $this->addMessageHolder(
            $this->createMock(MessageHolderInterface::class)
        );

        $this->object->onClear(new OnClearEventArgs($this->entityManager));

        static::assertSame(
            [],
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testOnClearSpecific(): void
    {
        $this->addMessageHolder(
            $messageHolder = $this->createMock(MessageHolderInterface::class)
        );

        $this->object->onClear(new OnClearEventArgs($this->entityManager, $messageHolder::class));

        static::assertCount(
            0,
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testOnClearOther(): void
    {
        $this->addMessageHolder(
            $this->createMock(MessageHolderInterface::class)
        );

        $this->object->onClear(new OnClearEventArgs($this->entityManager, \stdClass::class));

        static::assertCount(
            1,
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testPostFlushEmpty(): void
    {
        $this->envelopeFactory
            ->expects(static::never())
            ->method('createEnvelopes');

        $this->messageBus
            ->expects(static::never())
            ->method('dispatch');

        $this->object->postFlush();
    }

    public function testPostFlushOnlyUninitializedProxy(): void
    {
        $this->addMessageHolder(
            new class() implements Proxy,
                MessageHolderInterface {
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

        $this->envelopeFactory
            ->expects(static::never())
            ->method('createEnvelopes');

        $this->messageBus
            ->expects(static::never())
            ->method('dispatch');

        $this->object->postFlush();
    }

    public function testPostFlushWithOneMessage(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        $messageHolder->expects(static::once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn(
                $messages = [
                    $message1 = $this->createMock(LifeCycleAwareMessageInterface::class),
                    // Arbitrary interface just to add preSend method without implementing LifeCycleAwareMessageInterface
                    $message2 = $this->createMock(MockablePreSendAwareMessageInterface::class),
                ]
            );

        $message1
            ->expects(static::once())
            ->method('preSend')
            ->with($messageHolder);

        $message2
            ->expects(static::never())
            ->method('preSend');

        $this->envelopeFactory
            ->expects(static::once())
            ->method('createEnvelopes')
            ->with($messageHolder, $messages)
            ->willReturn([$envelope = new Envelope((object) [])]);

        $this->messageBus
            ->expects(static::once())
            ->method('dispatch')
            ->with($envelope)
            ->willReturnArgument(0);

        $this->object->postFlush();
    }

    public function testPostFlushWithMultipleMessageHolder(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        $messageHolder
            ->expects(static::once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn([(object) []]);

        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        $messageHolder
            ->expects(static::once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn([(object) []]);

        $this->envelopeFactory
            ->expects(static::exactly(2))
            ->method('createEnvelopes')
            ->willReturn([$envelope = new Envelope((object) [])]);

        $this->messageBus
            ->expects(static::exactly(2))
            ->method('dispatch')
            ->with($envelope)
            ->willReturnArgument(0);

        $this->object->postFlush();
    }

    public function testReset(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        static::assertSame(
            [$messageHolder],
            $this->object->getFlattenMessageHolders()
        );

        $this->object->reset();

        static::assertSame(
            [],
            $this->object->getFlattenMessageHolders()
        );
    }

    private function addMessageHolder(MessageHolderInterface $messageHolder): void
    {
        $messageHolders = ReflectionAccessor::getPropertyValue($this->object, 'messageHolders');
        $messageHolders[$messageHolder::class][spl_object_id($messageHolder)] = $messageHolder;

        ReflectionAccessor::setPropertyValue(
            $this->object,
            'messageHolders',
            $messageHolders
        );
    }
}
