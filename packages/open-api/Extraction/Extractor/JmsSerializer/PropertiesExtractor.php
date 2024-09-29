<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\Event\PropertyExtractedEvent;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\ArrayHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\DynamicObjectHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\EnumHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\GenericTemplateHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\TypeToSchemaHandlerInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Schema;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Exclusion\VersionExclusionStrategy;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use phpDocumentor\Reflection\DocBlockFactory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PropertiesExtractor implements ExtractorInterface
{
    final public const CONTEXT_PARAMETER_ENABLE_VERSION_EXCLUSION_STRATEGY = 'jms-enable-version-exclusion-strategy';

    /**
     * @var array|TypeToSchemaHandlerInterface[]
     */
    private iterable $typeToSchemaHandlers;

    public static function getDefaultPriority(): int
    {
        return 128;
    }

    public function __construct(
        private MetadataFactoryInterface $factory,
        private PropertyNamingStrategyInterface $namingStrategy,
        private EventDispatcherInterface $eventDispatcher,
        ?iterable $typeToSchemaHandlers = null,
    ) {
        $this->typeToSchemaHandlers = $typeToSchemaHandlers ?: $this::getDefaultHandlers();
    }

    public static function getDefaultHandlers(): array
    {
        return [
            new DynamicObjectHandler(),
            new ArrayHandler(),
            new GenericTemplateHandler(),
            new EnumHandler(),
        ];
    }

    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof \ReflectionClass) {
            return false;
        }

        if (!$target instanceof Schema) {
            return false;
        }

        return null !== $this->factory->getMetadataForClass($source->getName());
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param \ReflectionClass $source
     * @param Schema           $target
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $meta = $this->factory->getMetadataForClass($source->getName());

        $exclusionStrategies = [];

        $subContext = $extractionContext->createSubContext();

        $modelContext = $subContext->getParameter('model-context', []);

        if (isset($modelContext['serializer-groups'])) {
            $exclusionStrategies[] = new GroupsExclusionStrategy($modelContext['serializer-groups']);
        }

        if ($extractionContext->getParameter(self::CONTEXT_PARAMETER_ENABLE_VERSION_EXCLUSION_STRATEGY)) {
            $info = $extractionContext->getRootSchema()->info;
            if (!isset($info->version)) {
                throw new \RuntimeException('You must specify the [swagger.info.version] if you activate jms version exclusion strategy.');
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
                if (!isset($propertyMetadata->type['name'])) {
                    throw new \RuntimeException(\sprintf('Type of property [%s::%s] is not set', $propertyMetadata->class, $propertyMetadata->name));
                }
                $propertySchema = static::extractTypeSchema(
                    $propertyMetadata->type['name'],
                    $subContext,
                    $propertyMetadata
                );
            }

            if ($propertyMetadata->readOnly) {
                $propertySchema->readOnly = true;
            }

            $name = $this->namingStrategy->translateName($propertyMetadata);
            $target->properties[$name] = $propertySchema;
            $propertySchema->description = $this->getDescription($propertyMetadata) ?: null;

            $this->eventDispatcher->dispatch(new PropertyExtractedEvent($propertyMetadata, $propertySchema));
        }
    }

    public static function extractTypeSchema(
        string $type,
        ExtractionContextInterface $extractionContext,
        PropertyMetadata $propertyMetadata,
    ): Schema {
        $extractionContext = $extractionContext->createSubContext();
        $path = $extractionContext->getParameter('jms-path', []);
        $path[] = $propertyMetadata;
        $extractionContext->setParameter('jms-path', $path);
        $extractionContext->getOpenApi()->extract($type, $schema = new Schema(), $extractionContext);

        return $schema;
    }

    private function getDescription(PropertyMetadata $item): string
    {
        $factory = DocBlockFactory::createInstance();

        $ref = new \ReflectionClass($item->class);
        try {
            if ($item instanceof VirtualPropertyMetadata) {
                $docComment = $ref->getMethod($item->getter)->getDocComment();
            } else {
                $docComment = $ref->getProperty($item->name)->getDocComment();
            }

            if (!$docComment) {
                return '';
            }

            return $factory->create($docComment)->getSummary();
        } catch (\ReflectionException) {
            return '';
        }
    }

    /**
     * @param ExclusionStrategyInterface[] $exclusionStrategies
     */
    private function shouldSkipProperty(
        array $exclusionStrategies,
        PropertyMetadata $item,
        ExtractionContextInterface $extractionContext,
    ): bool {
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
