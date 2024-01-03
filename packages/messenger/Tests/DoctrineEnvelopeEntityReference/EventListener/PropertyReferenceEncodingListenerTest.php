<?php

namespace Draw\Component\Messenger\Tests\DoctrineEnvelopeEntityReference\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\EventListener\PropertyReferenceEncodingListener;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostDecodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostEncodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PreEncodeEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PropertyReferenceEncodingListenerTest extends TestCase
{
    private PropertyReferenceEncodingListener $object;

    protected function setUp(): void
    {
        $this->object = new PropertyReferenceEncodingListener(
            $this->createMock(ManagerRegistry::class),
            $this->createMock(ManagerRegistry::class)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object,
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                PreEncodeEvent::class => 'createPropertyReferenceStamps',
                PostEncodeEvent::class => 'restoreDoctrineObjects',
                PostDecodeEvent::class => 'restoreDoctrineObjects',
            ],
            $this->object::getSubscribedEvents()
        );
    }
}
