<?php

namespace Draw\Component\OpenApi\Tests\Serializer;

use Draw\Component\OpenApi\Serializer\Serialization;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Serialization::class)]
class SerializationTest extends TestCase
{
    private Serialization $object;

    private int $statusCode;

    private array $serializerGroups;

    private bool $serializerEnableMaxDepthChecks;

    private string $serializerVersion;

    private array $contextAttributes;

    protected function setUp(): void
    {
        $this->object = new Serialization(
            statusCode: $this->statusCode = random_int(100, 599),
            serializerGroups: $this->serializerGroups = [uniqid('group-')],
            serializerEnableMaxDepthChecks: $this->serializerEnableMaxDepthChecks = true,
            serializerVersion: $this->serializerVersion = uniqid('version-'),
            contextAttributes: $this->contextAttributes = [uniqid('key-') => uniqid('value-')],
        );
    }

    public function testStatusConstructorValue(): void
    {
        static::assertSame(
            $this->statusCode,
            $this->object->statusCode
        );
    }

    public function testSerializerGroupsConstructorValue(): void
    {
        static::assertSame(
            $this->serializerGroups,
            $this->object->serializerGroups
        );
    }

    public function testSerializerEnableMaxDepthChecksConstructorValue(): void
    {
        static::assertSame(
            $this->serializerEnableMaxDepthChecks,
            $this->object->serializerEnableMaxDepthChecks
        );
    }

    public function testSerializerVersionConstructorValue(): void
    {
        static::assertSame(
            $this->serializerVersion,
            $this->object->serializerVersion
        );
    }

    public function testContextAttributesConstructorValue(): void
    {
        static::assertSame(
            $this->contextAttributes,
            $this->object->contextAttributes
        );
    }
}
