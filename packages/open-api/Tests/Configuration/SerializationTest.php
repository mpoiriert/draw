<?php

namespace Draw\Component\OpenApi\Tests\Configuration;

use Draw\Component\OpenApi\Configuration\Serialization;
use Draw\Component\OpenApi\Schema\Header;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * @covers \Draw\Component\OpenApi\Configuration\Serialization
 */
class SerializationTest extends TestCase
{
    private Serialization $object;

    private int $statusCode;

    private array $serializerGroups;

    private bool $serializerEnableMaxDepthChecks;

    private string $serializerVersion;

    private array $headers;

    private array $contextAttributes;

    public function setUp(): void
    {
        $this->object = new Serialization([
            'statusCode' => $this->statusCode = rand(100, 599),
            'serializerGroups' => $this->serializerGroups = [uniqid('group-')],
            'serializerEnableMaxDepthChecks' => $this->serializerEnableMaxDepthChecks = true,
            'serializerVersion' => $this->serializerVersion = uniqid('version-'),
            'headers' => $this->headers = [new Header()],
            'contextAttributes' => $this->contextAttributes = [uniqid('key-') => uniqid('value-')],
        ]);
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ConfigurationAnnotation::class,
            $this->object
        );
    }

    public function testStatusCodeMutator(): void
    {
        static::assertSame(
            $this->statusCode,
            $this->object->getStatusCode()
        );

        $this->object->setStatusCode($value = rand(PHP_INT_MIN, PHP_INT_MAX));

        static::assertSame(
            $value,
            $this->object->getStatusCode()
        );
    }

    public function testSerializerGroupsMutator(): void
    {
        static::assertSame(
            $this->serializerGroups,
            $this->object->getSerializerGroups()
        );

        $this->object->setSerializerGroups($value = [uniqid('group-')]);

        static::assertSame(
            $value,
            $this->object->getSerializerGroups()
        );
    }

    public function testSerializerEnableMaxDepthChecksMutator(): void
    {
        static::assertSame(
            $this->serializerEnableMaxDepthChecks,
            $this->object->getSerializerEnableMaxDepthChecks()
        );

        $this->object->setSerializerEnableMaxDepthChecks(false);

        static::assertSame(
            false,
            $this->object->getSerializerEnableMaxDepthChecks()
        );
    }

    public function testSerializerVersionMutator(): void
    {
        static::assertSame(
            $this->serializerVersion,
            $this->object->getSerializerVersion()
        );

        $this->object->setSerializerVersion($value = uniqid());

        static::assertSame(
            $value,
            $this->object->getSerializerVersion()
        );
    }

    public function testHeadersMutator(): void
    {
        static::assertSame(
            $this->headers,
            $this->object->getHeaders()
        );

        $this->object->setHeaders($value = [new Header()]);

        static::assertSame(
            $value,
            $this->object->getHeaders()
        );
    }

    public function testContextAttributesMutator(): void
    {
        static::assertSame(
            $this->contextAttributes,
            $this->object->getContextAttributes()
        );

        $this->object->setContextAttributes($value = [uniqid('key-') => uniqid('value-')]);

        static::assertSame(
            $value,
            $this->object->getContextAttributes()
        );
    }

    public function testGetAliasName(): void
    {
        static::assertSame(
            'draw_open_api_serialization',
            $this->object->getAliasName()
        );
    }

    public function testAllowArray(): void
    {
        static::assertFalse($this->object->allowArray());
    }
}
