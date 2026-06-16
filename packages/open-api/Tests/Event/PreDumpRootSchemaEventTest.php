<?php

namespace Draw\Component\OpenApi\Tests\Event;

use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\Schema\Root;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PreDumpRootSchemaEvent::class)]
class PreDumpRootSchemaEventTest extends TestCase
{
    private PreDumpRootSchemaEvent $object;

    private Root $schema;

    protected function setUp(): void
    {
        $this->object = new PreDumpRootSchemaEvent(
            $this->schema = new Root()
        );
    }

    public function testGetSchema(): void
    {
        static::assertSame(
            $this->schema,
            $this->object->getSchema()
        );
    }
}
