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
        $this->assertInstanceOf(
            ConfigurationAnnotation::class,
            $this->object
        );
    }

    public function testStatusCodeMutator(): void
    {
        $this->assertSame(
            $this->statusCode,
            $this->object->getStatusCode()
        );

        $this->object->setStatusCode($value = rand(PHP_INT_MIN, PHP_INT_MAX));

        $this->assertSame(
            $value,
            $this->object->getStatusCode()
        );
    }

    public function testSerializerGroupsMutator(): void
    {
        $this->assertSame(
            $this->serializerGroups,
            $this->object->getSerializerGroups()
        );

        $this->object->setSerializerGroups($value = [uniqid('group-')]);

        $this->assertSame(
            $value,
            $this->object->getSerializerGroups()
        );
    }

    public function testSerializerEnableMaxDepthChecksMutator(): void
    {
        $this->assertSame(
            $this->serializerEnableMaxDepthChecks,
            $this->object->getSerializerEnableMaxDepthChecks()
        );

        $this->object->setSerializerEnableMaxDepthChecks(false);

        $this->assertSame(
            false,
            $this->object->getSerializerEnableMaxDepthChecks()
        );
    }

    public function testSerializerVersionMutator(): void
    {
        $this->assertSame(
            $this->serializerVersion,
            $this->object->getSerializerVersion()
        );

        $this->object->setSerializerVersion($value = uniqid());

        $this->assertSame(
            $value,
            $this->object->getSerializerVersion()
        );
    }

    public function testHeadersMutator(): void
    {
        $this->assertSame(
            $this->headers,
            $this->object->getHeaders()
        );

        $this->object->setHeaders($value = [new Header()]);

        $this->assertSame(
            $value,
            $this->object->getHeaders()
        );
    }

    public function testContextAttributesMutator(): void
    {
        $this->assertSame(
            $this->contextAttributes,
            $this->object->getContextAttributes()
        );

        $this->object->setContextAttributes($value = [uniqid('key-') => uniqid('value-')]);

        $this->assertSame(
            $value,
            $this->object->getContextAttributes()
        );
    }

    public function testGetAliasName(): void
    {
        $this->assertSame(
            'draw_open_api_serialization',
            $this->object->getAliasName()
        );
    }

    public function testAllowArray(): void
    {
        $this->assertFalse($this->object->allowArray());
    }
}
