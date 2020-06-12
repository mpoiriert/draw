<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor\JmsSerializer;

use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PropertiesExtractorTest extends TestCase
{
    /**
     * @var PropertiesExtractor
     */
    private $jmsExtractor;

    public function provideTestCanExtract()
    {
        return [
            [null, null, false],
            [null, new Schema(), false],
            [__NAMESPACE__.'\JmsExtractorStubModel', null, false],
            [__NAMESPACE__.'\JmsExtractorStubModel', new Schema(), true],
        ];
    }

    public function setUp()
    {
        $serializer = SerializerBuilder::create()->build();

        $serializer->serialize([], 'json', $context = new SerializationContext());

        $this->jmsExtractor = new PropertiesExtractor(
            $context->getMetadataFactory(),
            new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy())
        );
    }

    /**
     * @dataProvider provideTestCanExtract
     *
     * @param $source
     * @param $type
     * @param $canBeExtract
     */
    public function testCanExtract($source, $type, $canBeExtract)
    {
        if (null !== $source) {
            $source = new ReflectionClass($source);
        }

        /** @var ExtractionContextInterface $context */
        $context = $this->getMockForAbstractClass(ExtractionContextInterface::class);

        $this->assertSame($canBeExtract, $this->jmsExtractor->canExtract($source, $type, $context));

        if (!$canBeExtract) {
            try {
                $this->jmsExtractor->extract($source, $type, $context);
                $this->fail('should throw a exception of type [Draw\Component\OpenApi\Extraction\ExtractionImpossibleException]');
            } catch (ExtractionImpossibleException $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function testExtract()
    {
        $reflectionClass = new ReflectionClass(__NAMESPACE__.'\JmsExtractorStubModel');

        $context = $this->getExtractionContext();

        //Need to be there to validate that JMS extract it's type properly
        $openApi = $context->getOpenApi();
        $openApi->registerExtractor(new TypeSchemaExtractor());
        $openApi->registerExtractor($this->jmsExtractor);

        $context->setParameter('model-context', ['serializer-groups' => ['test']]);
        $schema = $context->getRootSchema();

        $schema->addDefinition($reflectionClass->getName(), $modelSchema = new Schema());

        $this->jmsExtractor->extract($reflectionClass, $modelSchema, $context);

        $jsonSchema = $context->getOpenApi()->dump($context->getRootSchema(), false);

        $this->assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/jmsExtractorTestExtract.json'),
            $jsonSchema
        );
    }

    public function getExtractionContext()
    {
        $openApi = new OpenApi();
        $schema = $openApi->extract('{"swagger":"2.0","definitions":{}}');

        return new ExtractionContext($openApi, $schema);
    }
}

class JmsExtractorStubModel
{
    /**
     * The name.
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups("test")
     * @Serializer\ReadOnly()
     */
    public $name;

    /**
     * @var
     *
     * @Serializer\Type("Draw\Component\OpenApi\Tests\Extraction\Extractor\JmsSerializer\JmsExtractorStubGeneric<string>")
     * @Serializer\Groups("test")
     * @Serializer\ReadOnly()
     */
    public $generic;

    /**
     * Serialized property.
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("serializeProperty")
     * @Serializer\Groups("test")
     */
    public $serializeProperty;

    /**
     * The array.
     *
     * @var array
     * @Serializer\Type("array<Draw\Component\OpenApi\Tests\Extraction\Extractor\JmsSerializer\JmsExtractorStubModel>")
     * @Serializer\Groups("test")
     */
    public $array;

    /**
     * The array.
     *
     * @var array
     * @Serializer\Type("array<Draw\Component\OpenApi\Tests\Extraction\Extractor\JmsSerializer\JmsExtractorStubModel>")
     */
    public $notThereByGroup;

    /**
     * @var string
     * @Serializer\Exclude()
     * @Serializer\Groups("test")
     */
    public $notThere;

    /**
     * The virtual property.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\Type(JmsExtractorStubModel::class)
     * @Serializer\Groups("test")
     */
    public function getVirtual()
    {
    }
}

class JmsExtractorStubGeneric
{
    /**
     * The generic property.
     *
     * @var string
     * @Serializer\Type("generic")
     * @Serializer\Groups("test")
     * @Serializer\ReadOnly()
     */
    public $name;
}
