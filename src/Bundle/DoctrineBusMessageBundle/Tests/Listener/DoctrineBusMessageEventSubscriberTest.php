<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Tests\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Draw\Bundle\DoctrineBusMessageBundle\EnvelopeFactory\EnvelopeFactoryInterface;
use Draw\Bundle\DoctrineBusMessageBundle\Listener\DoctrineBusMessageEventSubscriber;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DoctrineBusMessageEventSubscriberTest extends TestCase implements MessageHolderInterface
{
    use MessageHolderTrait;

    private $doctrineBusMessageEventSubscriber;

    private $envelopeFactory;

    private $event;

    private $messageBus;

    private $unitOfWork;

    public function setUp(): void
    {
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->event = $this->prophesize(PostFlushEventArgs::class);
        $this->unitOfWork = $this->prophesize(UnitOfWork::class);
        $this->envelopeFactory = $this->prophesize(EnvelopeFactoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $entityManager->getUnitOfWork()->shouldBeCalledOnce()->willReturn($this->unitOfWork);
        $this->event->getEntityManager()->shouldBeCalledOnce()->willReturn($entityManager);

        $this->doctrineBusMessageEventSubscriber = new DoctrineBusMessageEventSubscriber(
            $this->messageBus->reveal(),
            $this->envelopeFactory->reveal()
        );
    }

    public function testPostFlushEmpty(): void
    {
        $this->unitOfWork->getIdentityMap()->shouldBeCalledOnce()->willReturn([]);
        $this->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
        $this->doctrineBusMessageEventSubscriber->postFlush($this->event->reveal());
    }

    public function testPostRemoveNoMessageHolder(): void
    {
        $this->unitOfWork->getIdentityMap()->shouldBeCalledOnce()->willReturn([[new stdClass()]]);
        $this->messageBus->dispatch(Argument::any())->shouldNotBeCalled();
        $this->doctrineBusMessageEventSubscriber->postFlush($this->event->reveal());
    }

    public function testPostRemoveOneMessageHolderWithNoMessage(): void
    {
        $this->unitOfWork->getIdentityMap()->shouldBeCalledOnce()->willReturn([[$this]]);
        $this->doctrineBusMessageEventSubscriber->postFlush($this->event->reveal());

        $this->assertEmpty($this->messageQueue());
    }

    public function testPostRemoveOneMessageHolderWithOneMessage(): void
    {
        $this->messageQueue()->enqueue($message = new stdClass());
        $this->envelopeFactory
            ->createEnvelopes($this, [$message])
            ->shouldBeCalledOnce()
            ->willReturn([$envelope = new Envelope($message)]);
        $this->unitOfWork->getIdentityMap()->shouldBeCalledOnce()->willReturn([[$this]]);
        $this->messageBus->dispatch($envelope)->shouldBeCalledOnce()->willReturnArgument();
        $this->doctrineBusMessageEventSubscriber->postFlush($this->event->reveal());

        $this->assertEmpty($this->messageQueue());
    }

    public function testPostRemoveOneMessageHolderWithOneMessagePrecedeByNoneMessageHolder(): void
    {
        $this->messageQueue()->enqueue($message = new stdClass());
        $this->envelopeFactory
            ->createEnvelopes($this, [$message])
            ->shouldBeCalledOnce()
            ->willReturn([$envelope = new Envelope($message)]);
        $this->unitOfWork->getIdentityMap()->shouldBeCalledOnce()->willReturn([[new stdClass(), $this]]);
        $this->messageBus->dispatch($envelope)->shouldBeCalledOnce()->willReturnArgument();
        $this->doctrineBusMessageEventSubscriber->postFlush($this->event->reveal());

        $this->assertEmpty($this->messageQueue());
    }
}
