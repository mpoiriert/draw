<?php

namespace Draw\Component\OpenApi\Extraction\Extractor;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Naming\ClassNamingFilterInterface;
use Draw\Component\OpenApi\Schema\Schema;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Collection;

class TypeSchemaExtractor implements ExtractorInterface
{
    private array $definitionHashes = [];

    private static ?TypeResolver $typeResolver = null;

    public static function getDefaultPriority(): int
    {
        return 128;
    }

    /**
     * @param ClassNamingFilterInterface[] $classNamingFilters
     */
    public function __construct(private array $classNamingFilters = [])
    {
    }

    /**
     * @param string $source
     * @param Schema $target
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$target instanceof Schema) {
            return false;
        }

        if (null === static::getPrimitiveType($source, $extractionContext)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $source
     * @param Schema $target
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext): void
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $primitiveType = static::getPrimitiveType($source, $extractionContext);

        $target->type = $primitiveType['type'];

        if ('array' === $target->type) {
            $target->items = $itemsSchema = new Schema();
            if (isset($primitiveType['subType'])) {
                $extractionContext->getOpenApi()->extract(
                    $primitiveType['subType'],
                    $itemsSchema,
                    $extractionContext
                );
            }

            return;
        }

        if ('generic' === $target->type) {
            $target->type = 'object';
            $reflectionClass = new \ReflectionClass($primitiveType['class']);
            $subContext = $extractionContext->createSubContext();
            $subContext->setParameter('generic-template', $primitiveType['template']);
            $extractionContext->getOpenApi()->extract(
                $reflectionClass,
                $target,
                $subContext
            );

            return;
        }

        if ('object' === $target->type) {
            $target->type = null;
            $reflectionClass = new \ReflectionClass($primitiveType['class']);
            $rootSchema = $extractionContext->getRootSchema();
            $context = $extractionContext->getParameter('model-context', []);

            $definitionName = $this->getDefinitionName($reflectionClass->name, $context);

            if ($hash = $this->getHash($definitionName, $context)) {
                $definitionName .= '?'.$hash;
            }

            if (!$rootSchema->hasDefinition($definitionName)) {
                $rootSchema->addDefinition($definitionName, $refSchema = clone $target);
                $refSchema->type = 'object';
                $extractionContext->getOpenApi()->extract(
                    $reflectionClass,
                    $refSchema,
                    $extractionContext
                );
            }

            $target->ref = $rootSchema->getDefinitionReference($definitionName);

            return;
        }

        if (isset($primitiveType['format'])) {
            $target->format = $primitiveType['format'];
        }
    }

    private function getDefinitionName(string $className, array $context): string
    {
        $newName = $className;
        foreach ($this->classNamingFilters as $classNamingFilter) {
            $newName = $classNamingFilter->filterClassName($className, $context, $newName);
        }

        return $newName;
    }

    private function getHash(string $modelName, ?array $context = null): string
    {
        $context = $context ?: [];

        $hash = md5(http_build_query($context));

        if (!\array_key_exists($modelName, $this->definitionHashes)) {
            $this->definitionHashes[$modelName] = [];
        }

        if (!\in_array($hash, $this->definitionHashes[$modelName], true)) {
            $this->definitionHashes[$modelName][] = $hash;
        }

        return array_search($hash, $this->definitionHashes[$modelName], true);
    }

    public static function getPrimitiveType(mixed $type, ?ExtractionContextInterface $extractionContext = null): ?array
    {
        if (!\is_string($type)) {
            return null;
        }

        if (str_starts_with($type, '?')) {
            $type = substr($type, 1);
        }

        if ('generic' === $type) {
            $type = $extractionContext->getParameter('generic-template');
        }

        $result = (self::$typeResolver ??= new TypeResolver())->resolve($type);

        if ($result instanceof Collection) {
            return [
                'type' => 'generic',
                'class' => (string) $result->getFqsen(),
                'template' => (string) $result->getValueType(),
            ];
        }

        $type = (string) $result;

        $primitiveType = [];

        $typeOfArray = str_replace('[]', '', $type);
        if ($typeOfArray !== $type) {
            if ($typeOfArray !== substr($type, 0, -2)) {
                return null;
            }

            $primitiveType['type'] = 'array';
            $primitiveType['subType'] = $typeOfArray;

            return $primitiveType;
        }

        $types = [
            'int' => ['type' => 'integer', 'format' => 'int32'],
            'integer' => ['type' => 'integer', 'format' => 'int32'],
            'long' => ['type' => 'integer', 'format' => 'int64'],
            'float' => ['type' => 'number', 'format' => 'float'],
            'double' => ['type' => 'number', 'format' => 'double'],
            'string' => ['type' => 'string'],
            'byte' => ['type' => 'string', 'format' => 'byte'],
            'boolean' => ['type' => 'boolean'],
            'bool' => ['type' => 'boolean'],
            'date' => ['type' => 'string', 'format' => 'date'],
            'DateTime' => ['type' => 'string', 'format' => 'date-time'],
            'DateTimeImmutable' => ['type' => 'string', 'format' => 'date-time'],
            '\DateTime' => ['type' => 'string', 'format' => 'date-time'],
            '\DateTimeImmutable' => ['type' => 'string', 'format' => 'date-time'],
            'dateTime' => ['type' => 'string', 'format' => 'date-time'],
            'password' => ['type' => 'string', 'format' => 'password'],
            'array' => ['type' => 'array'],
        ];

        if (\array_key_exists($type, $types)) {
            return $types[$type];
        }

        if (class_exists($type)) {
            return [
                'type' => 'object',
                'class' => $type,
            ];
        }

        return null;
    }
}
