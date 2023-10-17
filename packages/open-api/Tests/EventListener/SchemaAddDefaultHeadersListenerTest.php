<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\EventListener\SchemaAddDefaultHeadersListener;
use JMS\Serializer\ArrayTransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[CoversClass(SchemaAddDefaultHeadersListener::class)]
class SchemaAddDefaultHeadersListenerTest extends TestCase
{
    private SchemaAddDefaultHeadersListener $object;

    protected function setUp(): void
    {
        $this->object = new SchemaAddDefaultHeadersListener(
            [],
            $this->createMock(ArrayTransformerInterface::class)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testSubscribedEvents(): void
    {
        static::assertSame(
            [
                PreDumpRootSchemaEvent::class => ['addHeaders', 255],
            ],
            $this->object::getSubscribedEvents()
        );
    }
}
