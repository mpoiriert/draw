<?php

namespace Draw\Component\OpenApi\Tests\Configuration;

use Draw\Component\OpenApi\Configuration\Deserialization;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @covers \Draw\Component\OpenApi\Configuration\Deserialization
 */
class DeserializationTest extends TestCase
{
    private Deserialization $object;

    private array $deserializationGroups;

    private array $validationGroups;

    private array $propertiesMap;

    public function setUp(): void
    {
        $this->object = new Deserialization(
            [
                'validate' => true,
                'deserializationEnableMaxDepth' => true,
                'deserializationGroups' => $this->deserializationGroups = [uniqid('group-')],
                'validationGroups' => $this->validationGroups = [uniqid('group-')],
                'propertiesMap' => $this->propertiesMap = [uniqid('key-') => uniqid('value-')],
            ]
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ParamConverter::class,
            $this->object
        );
    }

    public function testPropertiesMapMutator(): void
    {
        static::assertSame(
            $this->propertiesMap,
            $this->object->getPropertiesMap()
        );

        $this->object->setPropertiesMap($value = [uniqid('key-') => uniqid('value-')]);

        static::assertSame(
            $value,
            $this->object->getPropertiesMap()
        );
    }

    public function testValidateMutator(): void
    {
        static::assertSame(
            true,
            $this->object->getValidate()
        );

        $this->object->setValidate(false);

        static::assertSame(
            false,
            $this->object->getValidate()
        );
    }

    public function testValidationGroupsMutator(): void
    {
        static::assertSame(
            $this->validationGroups,
            $this->object->getValidationGroups()
        );

        $this->object->setValidationGroups($value = [uniqid('group-')]);

        static::assertSame(
            $value,
            $this->object->getValidationGroups()
        );
    }

    public function testDeserializationGroupsMutator(): void
    {
        static::assertSame(
            $this->deserializationGroups,
            $this->object->getDeserializationGroups()
        );

        $this->object->setDeserializationGroups($value = [uniqid('group-')]);

        static::assertSame(
            $value,
            $this->object->getDeserializationGroups()
        );
    }

    public function testDeserializationEnableMaxDepthMutator(): void
    {
        static::assertSame(
            true,
            $this->object->getDeserializationEnableMaxDepth()
        );

        $this->object->setDeserializationEnableMaxDepth(false);

        static::assertSame(
            false,
            $this->object->getDeserializationEnableMaxDepth()
        );
    }
}
