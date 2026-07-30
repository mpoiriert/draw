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
use Draw\Component\Tester\DoubleTrait;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
#[CoversClass(DoctrineBusMessageListener::class)]
#[AllowMockObjectsWithoutExpectations]
class DoctrineBusMessageListenerTest extends TestCase
{
    use DoubleTrait;

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
        $this->assertInstanceOf(
            ResetInterface::class,
            $this->object
        );
    }

    public function testPostPersist(): void
    {
        $messageHolder = $this->createStub(MessageHolderInterface::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($messageHolder::class)
            ->willReturn($classMetadata = new ClassMetadata(uniqid()))
        ;

        $classMetadata->rootEntityName = $messageHolder::class;

        $this->object->postPersist(
            new LifecycleEventArgs(
                $messageHolder,
                $this->entityManager
            )
        );

        $this->assertSame(
            [$messageHolder],
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testPostPersistNotMessageHolderEntity(): void
    {
        $messageHolder = (object) [];

        $this->entityManager
            ->expects($this->never())
            ->method('getClassMetadata')
        ;

        $this->object->postPersist(
            new LifecycleEventArgs(
                $messageHolder,
                $this->entityManager
            )
        );

        $this->assertSame(
            [],
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testPostLoad(): void
    {
        $messageHolder = $this->createStub(MessageHolderInterface::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($messageHolder::class)
            ->willReturn($classMetadata = new ClassMetadata(uniqid()))
        ;

        $classMetadata->rootEntityName = $messageHolder::class;

        $this->object->postLoad(
            new LifecycleEventArgs(
                $messageHolder,
                $this->entityManager
            )
        );

        $this->assertSame(
            [$messageHolder],
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testOnClearAll(): void
    {
        $this->addMessageHolder(
            $this->createStub(MessageHolderInterface::class)
        );

        $this->object->onClear(new OnClearEventArgs($this->entityManager));

        $this->assertSame(
            [],
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testOnClear(): void
    {
        $this->addMessageHolder(
            $this->createStub(MessageHolderInterface::class)
        );

        $this->object->onClear(new OnClearEventArgs($this->entityManager));

        $this->assertCount(
            0,
            $this->object->getFlattenMessageHolders()
        );
    }

    public function testPostFlushEmpty(): void
    {
        $this->envelopeFactory
            ->expects($this->never())
            ->method('createEnvelopes')
        ;

        $this->messageBus
            ->expects($this->never())
            ->method('dispatch')
        ;

        $this->object->postFlush();
    }

    public function testPostFlushOnlyUninitializedProxy(): void
    {
        $this->addMessageHolder(
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

        $this->envelopeFactory
            ->expects($this->never())
            ->method('createEnvelopes')
        ;

        $this->messageBus
            ->expects($this->never())
            ->method('dispatch')
        ;

        $this->object->postFlush();
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
                    // Arbitrary interface just to add preSend method without implementing LifeCycleAwareMessageInterface
                    $message2 = $this->createMock(PreSendAwareMessageInterface::class),
                ]
            )
        ;

        $message1
            ->expects($this->once())
            ->method('preSend')
            ->with($messageHolder)
        ;

        $message2
            ->expects($this->never())
            ->method('preSend')
        ;

        $this->envelopeFactory
            ->expects($this->once())
            ->method('createEnvelopes')
            ->with($messageHolder, $messages)
            ->willReturn([$envelope = new Envelope((object) [])])
        ;

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($envelope)
            ->willReturnArgument(0)
        ;

        $this->object->postFlush();
    }

    public function testPostFlushWithMultipleMessageHolder(): void
    {
        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        $messageHolder
            ->expects($this->once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn([(object) []])
        ;

        $messageHolder = $this->createMock(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        $messageHolder
            ->expects($this->once())
            ->method('getOnHoldMessages')
            ->with(true)
            ->willReturn([(object) []])
        ;

        $this->envelopeFactory
            ->expects($this->exactly(2))
            ->method('createEnvelopes')
            ->willReturn([$envelope = new Envelope((object) [])])
        ;

        $this->messageBus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with($envelope)
            ->willReturnArgument(0)
        ;

        $this->object->postFlush();
    }

    public function testReset(): void
    {
        $messageHolder = $this->createStub(MessageHolderInterface::class);

        $this->addMessageHolder($messageHolder);

        $this->assertSame(
            [$messageHolder],
            $this->object->getFlattenMessageHolders()
        );

        $this->object->reset();

        $this->assertSame(
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
