<?php

namespace Draw\Component\OpenApi\Tests\Event;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\Schema\Root;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent
 */
class PreDumpRootSchemaEventTest extends TestCase
{
    private PreDumpRootSchemaEvent $object;

    private Root $schema;

    public function setUp(): void
    {
        $this->object = new PreDumpRootSchemaEvent(
            $this->schema = new Root()
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Event::class,
            $this->object
        );
    }

    public function testGetSchema(): void
    {
        $this->assertSame(
            $this->schema,
            $this->object->getSchema()
        );
    }
}
