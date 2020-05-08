<?php namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\Event\PropertyExtractedEvent;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\ArrayHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\DynamicObjectHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\GenericTemplateHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\TypeToSchemaHandlerInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Exclusion\VersionExclusionStrategy;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use JMS\Serializer\Metadata\PropertyMetadata;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PropertiesExtractor implements ExtractorInterface
{
    const CONTEXT_PARAMETER_ENABLE_VERSION_EXCLUSION_STRATEGY = 'jms-enable-version-exclusion-strategy';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MetadataFactoryInterface
     */
    private $factory;

    /**
     * @var PropertyNamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * @var array|TypeToSchemaHandlerInterface[]
     */
    private $typeToSchemaHandlers = [];

    public function __construct(
        MetadataFactoryInterface $factory,
        PropertyNamingStrategyInterface $namingStrategy,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->factory = $factory;
        $this->namingStrategy = $namingStrategy;
        $this->eventDispatcher = $eventDispatcher;

        $this->registerTypeToSchemaHandler(new DynamicObjectHandler());
        $this->registerTypeToSchemaHandler(new ArrayHandler());
        $this->registerTypeToSchemaHandler(new GenericTemplateHandler());
    }

    public function registerTypeToSchemaHandler(TypeToSchemaHandlerInterface $typeToSchemaHandler)
    {
        $this->typeToSchemaHandlers[] = $typeToSchemaHandler;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ReflectionClass) {
            return false;
        }

        if (!$type instanceof Schema) {
            return false;
        }

        return !is_null($this->factory->getMetadataForClass($source->getName()));
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param ReflectionClass $reflectionClass
     * @param Schema $schema
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($reflectionClass, $schema, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($reflectionClass, $schema, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $meta = $this->factory->getMetadataForClass($reflectionClass->getName());

        $exclusionStrategies = array();

        $subContext = $extractionContext->createSubContext();

        $modelContext = $subContext->getParameter('model-context', []);

        if (isset($modelContext['serializer-groups'])) {
            $exclusionStrategies[] = new GroupsExclusionStrategy($modelContext['serializer-groups']);
        }

        if ($extractionContext->getParameter(self::CONTEXT_PARAMETER_ENABLE_VERSION_EXCLUSION_STRATEGY)) {
            $info = $extractionContext->getRootSchema()->info;
            if (!isset($info->version)) {
                throw new RuntimeException(
                    'You must specify the [swagger.info.version] if you activate jms version exclusion strategy.'
                );
            }
            $exclusionStrategies[] = new VersionExclusionStrategy($extractionContext->getRootSchema()->info->version);
        }

        /** @var PropertyMetadata $propertyMetadata */
        foreach ($meta->propertyMetadata as $propertyMetadata) {
            // This is to prevent property of discriminator field name to not being complete
            if (isset($meta->discriminatorFieldName)
                && $propertyMetadata->name == $meta->discriminatorFieldName
                && !isset($propertyMetadata->type['name'])
            ) {
                $propertyMetadata->type = ['name' => 'string', 'params' => []];
            }

            if ($this->shouldSkipProperty($exclusionStrategies, $propertyMetadata, $subContext)) {
                continue;
            }

            $propertySchema = null;
            foreach ($this->typeToSchemaHandlers as $typeToSchemaHandler) {
                if ($propertySchema = $typeToSchemaHandler->extractSchemaFromType($propertyMetadata, $subContext)) {
                    break;
                }
            }

            if (!$propertySchema) {
                $propertySchema = $this->extractTypeSchema(
                    $propertyMetadata->type['name'],
                    $subContext,
                    $propertyMetadata
                );
            }

            if ($propertyMetadata->readOnly) {
                $propertySchema->readOnly = true;
            }

            $name = $this->namingStrategy->translateName($propertyMetadata);
            $schema->properties[$name] = $propertySchema;
            $propertySchema->description = (string)$this->getDescription($propertyMetadata) ?: null;

            if ($this->eventDispatcher) {
                $this->eventDispatcher->dispatch(new PropertyExtractedEvent($propertyMetadata, $propertySchema));
            }
        }
    }

    public static function extractTypeSchema(
        $type,
        ExtractionContextInterface $extractionContext,
        PropertyMetadata $propertyMetadata
    ) {
        $extractionContext = $extractionContext->createSubContext();
        $path = $extractionContext->getParameter('jms-path', []);
        $path[] = $propertyMetadata;
        $extractionContext->setParameter('jms-path', $path);
        $extractionContext->getOpenApi()->extract($type, $schema = new Schema(), $extractionContext);

        return $schema;
    }

    /**
     * @param PropertyMetadata $item
     * @return string
     */
    private function getDescription(PropertyMetadata $item)
    {
        $factory = DocBlockFactory::createInstance();

        $ref = new ReflectionClass($item->class);
        try {
            if ($item instanceof VirtualPropertyMetadata) {
                $docComment = $ref->getMethod($item->getter)->getDocComment();
            } else {
                $docComment = $ref->getProperty($item->name)->getDocComment();
            }

            if(!$docComment) {
                return '';
            }

            return $factory->create($docComment)->getSummary();
        } catch (ReflectionException $e) {
            return '';
        }
    }

    /**
     * @param ExclusionStrategyInterface[] $exclusionStrategies
     * @param PropertyMetadata $item
     * @param ExtractionContextInterface $extractionContext
     * @return bool
     */
    private function shouldSkipProperty(
        $exclusionStrategies,
        PropertyMetadata $item,
        ExtractionContextInterface $extractionContext
    ) {
        $serializationContext = SerializationContext::create();

        foreach ($extractionContext->getParameter('jms-path', []) as $metadata) {
            $serializationContext->getMetadataStack()->push($metadata);
        }

        foreach ($exclusionStrategies as $strategy) {
            if (true === $strategy->shouldSkipProperty($item, $serializationContext)) {
                return true;
            }
        }

        return false;
    }
}