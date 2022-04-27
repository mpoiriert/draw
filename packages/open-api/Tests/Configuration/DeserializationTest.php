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
        $this->assertInstanceOf(
            ParamConverter::class,
            $this->object
        );
    }

    public function testPropertiesMapMutator(): void
    {
        $this->assertSame(
            $this->propertiesMap,
            $this->object->getPropertiesMap()
        );

        $this->object->setPropertiesMap($value = [uniqid('key-') => uniqid('value-')]);

        $this->assertSame(
            $value,
            $this->object->getPropertiesMap()
        );
    }

    public function testValidateMutator(): void
    {
        $this->assertSame(
            true,
            $this->object->getValidate()
        );

        $this->object->setValidate(false);

        $this->assertSame(
            false,
            $this->object->getValidate()
        );
    }

    public function testValidationGroupsMutator(): void
    {
        $this->assertSame(
            $this->validationGroups,
            $this->object->getValidationGroups()
        );

        $this->object->setValidationGroups($value = [uniqid('group-')]);

        $this->assertSame(
            $value,
            $this->object->getValidationGroups()
        );
    }

    public function testDeserializationGroupsMutator(): void
    {
        $this->assertSame(
            $this->deserializationGroups,
            $this->object->getDeserializationGroups()
        );

        $this->object->setDeserializationGroups($value = [uniqid('group-')]);

        $this->assertSame(
            $value,
            $this->object->getDeserializationGroups()
        );
    }

    public function testDeserializationEnableMaxDepthMutator(): void
    {
        $this->assertSame(
            true,
            $this->object->getDeserializationEnableMaxDepth()
        );

        $this->object->setDeserializationEnableMaxDepth(false);

        $this->assertSame(
            false,
            $this->object->getDeserializationEnableMaxDepth()
        );
    }
}
