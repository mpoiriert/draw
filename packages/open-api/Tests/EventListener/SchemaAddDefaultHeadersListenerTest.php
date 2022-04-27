<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\EventListener\SchemaAddDefaultHeadersListener;
use JMS\Serializer\ArrayTransformerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @covers \Draw\Component\OpenApi\EventListener\SchemaAddDefaultHeadersListener
 */
class SchemaAddDefaultHeadersListenerTest extends TestCase
{
    private SchemaAddDefaultHeadersListener $object;

    public function setUp(): void
    {
        $this->object = new SchemaAddDefaultHeadersListener(
            [],
            $this->createMock(ArrayTransformerInterface::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testSubscribedEvents(): void
    {
        $this->assertSame(
            [
                PreDumpRootSchemaEvent::class => ['addHeaders', 255],
            ],
            $this->object::getSubscribedEvents()
        );
    }
}
