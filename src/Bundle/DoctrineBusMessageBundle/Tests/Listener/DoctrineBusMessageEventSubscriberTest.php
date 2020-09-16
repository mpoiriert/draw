<?php namespace Draw\Bundle\DoctrineBusMessageBundle\Tests\Listener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Draw\Bundle\DoctrineBusMessageBundle\Listener\DoctrineBusMessageEventSubscriber;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderInterface;
use Draw\Bundle\DoctrineBusMessageBundle\MessageHolderTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DoctrineBusMessageEventSubscriberTest extends TestCase implements MessageHolderInterface
{
    use MessageHolderTrait;

    private $doctrineBusMessageEventSubscriber;

    private $messageBus;

    private $event;

    public function setUp(): void
    {
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->event = $this->prophesize(LifecycleEventArgs::class);
        $this->event->getObject()->shouldBeCalledOnce()->willReturn($this);

        $this->doctrineBusMessageEventSubscriber = new DoctrineBusMessageEventSubscriber($this->messageBus->reveal());
    }

    public function testPostPersist_empty()
    {
        $this->messageBus->dispatch()->shouldNotBeCalled();
        $this->doctrineBusMessageEventSubscriber->postPersist($this->event->reveal());
    }

    public function testPostUpdate_empty()
    {
        $this->messageBus->dispatch()->shouldNotBeCalled();
        $this->doctrineBusMessageEventSubscriber->postUpdate($this->event->reveal());
    }

    public function testPostRemove_empty()
    {
        $this->messageBus->dispatch()->shouldNotBeCalled();
        $this->doctrineBusMessageEventSubscriber->postRemove($this->event->reveal());
    }

    public function testPostPersist_notEmpty()
    {
        $this->messageQueue()->enqueue($value = new \stdClass());
        $this->messageBus->dispatch($value)->willReturn(new Envelope($value))->shouldBeCalledOnce();

        $this->doctrineBusMessageEventSubscriber->postPersist($this->event->reveal());

        $this->assertTrue($this->messageQueue()->isEmpty());
    }

    public function testPostUpdate_notEmpty()
    {
        $this->messageQueue()->enqueue($value = new \stdClass());
        $this->messageBus->dispatch($value)->willReturn(new Envelope($value))->shouldBeCalledOnce();

        $this->doctrineBusMessageEventSubscriber->postUpdate($this->event->reveal());

        $this->assertTrue($this->messageQueue()->isEmpty());
    }

    public function testPostRemove_notEmpty()
    {
        $this->messageQueue()->enqueue($value = new \stdClass());
        $this->messageBus->dispatch($value)->willReturn(new Envelope($value))->shouldBeCalledOnce();

        $this->doctrineBusMessageEventSubscriber->postRemove($this->event->reveal());

        $this->assertTrue($this->messageQueue()->isEmpty());
    }
}