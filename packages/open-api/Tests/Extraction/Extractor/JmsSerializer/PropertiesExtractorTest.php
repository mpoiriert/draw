<?php

namespace Draw\Component\OpenApi\Tests\Extraction\Extractor\JmsSerializer;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Schema\Schema;
use Draw\Component\Tester\MockTrait;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @internal
 */
#[CoversClass(PropertiesExtractor::class)]
class PropertiesExtractorTest extends TestCase
{
    use MockTrait;

    private PropertiesExtractor $jmsExtractor;

    public static function provideTestCanExtract(): iterable
    {
        return [
            [null, null, false],
            [null, new Schema(), false],
            [__NAMESPACE__.'\JmsExtractorStubModel', null, false],
            [__NAMESPACE__.'\JmsExtractorStubModel', new Schema(), true],
        ];
    }

    protected function setUp(): void
    {
        $serializer = SerializerBuilder::create()->build();

        $serializer->serialize([], 'json', $context = new SerializationContext());

        $this->jmsExtractor = new PropertiesExtractor(
            $context->getMetadataFactory(),
            new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy()),
            new EventDispatcher()
        );
    }

    #[DataProvider('provideTestCanExtract')]
    public function testCanExtract(mixed $source, mixed $type, bool $canBeExtract): void
    {
        if (null !== $source) {
            $source = new \ReflectionClass($source);
        }

        static::assertSame(
            $canBeExtract,
            $this->jmsExtractor->canExtract(
                $source,
                $type,
                $context = $this->createMock(ExtractionContextInterface::class)
            )
        );

        if (!$canBeExtract) {
            try {
                $this->jmsExtractor->extract($source, $type, $context);
                static::fail('should throw a exception of type [Draw\Component\OpenApi\Exception\ExtractionImpossibleException]');
            } catch (ExtractionImpossibleException) {
                static::assertTrue(true);
            }
        }
    }

    public function testExtract(): void
    {
        $reflectionClass = new \ReflectionClass(__NAMESPACE__.'\JmsExtractorStubModel');

        // Need to be there to validate that JMS extract it's type properly
        $context = $this->getExtractionContext([
            new TypeSchemaExtractor(),
            $this->jmsExtractor,
        ]);

        $context->setParameter('model-context', ['serializer-groups' => ['test']]);
        $schema = $context->getRootSchema();

        $schema->addDefinition($reflectionClass->getName(), $modelSchema = new Schema());

        $this->jmsExtractor->extract($reflectionClass, $modelSchema, $context);

        $jsonSchema = $context->getOpenApi()->dump($context->getRootSchema(), false);

        static::assertJsonStringEqualsJsonString(
            file_get_contents(__DIR__.'/fixture/jmsExtractorTestExtract.json'),
            $jsonSchema
        );
    }

    public function getExtractionContext(array $extractors = []): ExtractionContext
    {
        $openApi = new OpenApi($extractors);
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
     */
    #[Serializer\Type('string')]
    #[Serializer\Groups(['test'])]
    #[Serializer\ReadOnlyProperty]
    public $name;

    #[Serializer\Type(JmsExtractorStubGeneric::class.'<string>')]
    #[Serializer\Groups(['test'])]
    #[Serializer\ReadOnlyProperty]
    public $generic;

    /**
     * The simple enum.
     *
     * @var JmsExtractorStubEnum
     */
    #[Serializer\Type('enum<\''.JmsExtractorStubEnum::class.'\'>')]
    #[Serializer\Groups(['test'])]
    public $genericEnum;

    /**
     * The backed enum.
     *
     * @var JmsExtractorStubEnum
     */
    #[Serializer\Type('enum<\''.JmsExtractorStubBackedEnum::class.'\'>')]
    #[Serializer\Groups(['test'])]
    public $backedEnum;

    /**
     * The backed enum int.
     *
     * @var JmsExtractorStubEnum
     */
    #[Serializer\Type('enum<\''.JmsExtractorStubBackedEnumInt::class.'\'>')]
    #[Serializer\Groups(['test'])]
    public $backedEnumInt;

    /**
     * Serialized property.
     *
     * @var string
     */
    #[Serializer\Type('string')]
    #[Serializer\SerializedName('serializeProperty')]
    #[Serializer\Groups(['test'])]
    public $serializeProperty;

    /**
     * The array.
     *
     * @var array
     */
    #[Serializer\Type('array<'.self::class.'>')]
    #[Serializer\Groups(['test'])]
    public $array;

    /**
     * The array.
     *
     * @var array
     */
    #[Serializer\Type('array<'.self::class.'>')]
    public $notThereByGroup;

    /**
     * @var string
     */
    #[Serializer\Exclude]
    #[Serializer\Groups(['test'])]
    public $notThere;

    /**
     * The virtual property.
     */
    #[Serializer\VirtualProperty]
    #[Serializer\Type(self::class)]
    #[Serializer\Groups(['test'])]
    public function getVirtual(): void
    {
    }
}

class JmsExtractorStubGeneric
{
    /**
     * The generic property.
     *
     * @var string
     */
    #[Serializer\Type('generic')]
    #[Serializer\Groups(['test'])]
    #[Serializer\ReadOnlyProperty]
    public $name;
}

enum JmsExtractorStubEnum
{
    case FOO;
    case BAR;
}
enum JmsExtractorStubBackedEnum: string
{
    case ABC = 'abc';
    case DEF = 'def';
}

enum JmsExtractorStubBackedEnumInt: int
{
    case ABC = 1;
    case DEF = 2;
}
