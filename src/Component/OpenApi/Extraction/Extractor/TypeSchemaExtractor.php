<?php

namespace Draw\Component\OpenApi\Extraction\Extractor;

use Draw\Component\OpenApi\Extraction\ExtractionContextInterface;
use Draw\Component\OpenApi\Extraction\ExtractionImpossibleException;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Schema\Schema;
use Draw\Component\OpenApi\Schema\Schema as SupportedTarget;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Collection;
use ReflectionClass;

class TypeSchemaExtractor implements ExtractorInterface
{
    /**
     * @var string[]
     */
    private $definitionAliases = [];

    private $definitionHashes = [];

    private static $typeResolver;

    public function registerDefinitionAlias($definition, $alias)
    {
        $this->definitionAliases[$definition] = $alias;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param SupportedTarget $target
     *
     * @return bool
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$target instanceof SupportedTarget) {
            return false;
        }

        if (null === self::getPrimitiveType($source, $extractionContext)) {
            return false;
        }

        return true;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param string          $source
     * @param SupportedTarget $target
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $primitiveType = self::getPrimitiveType($source, $extractionContext);

        $target->type = $primitiveType['type'];

        if ('array' == $target->type) {
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

        if ('generic' == $target->type) {
            $target->type = 'object';
            $reflectionClass = new ReflectionClass($primitiveType['class']);
            $subContext = $extractionContext->createSubContext();
            $subContext->setParameter('generic-template', $primitiveType['template']);
            $extractionContext->getOpenApi()->extract(
                $reflectionClass,
                $target,
                $subContext
            );

            return;
        }

        if ('object' == $target->type) {
            $target->type = null;
            $reflectionClass = new ReflectionClass($primitiveType['class']);
            $rootSchema = $extractionContext->getRootSchema();
            $context = $extractionContext->getParameter('model-context', []);

            $definitionName = $this->getDefinitionName($reflectionClass->name);

            if ($hash = $this->getHash($definitionName, $context)) {
                $definitionName .= '?'.$hash;
            }

            if (!$rootSchema->hasDefinition($definitionName)) {
                $rootSchema->addDefinition($definitionName, $refSchema = new Schema());
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

    private function getDefinitionName($className)
    {
        foreach ($this->definitionAliases as $class => $alias) {
            if ('\\' == substr($class, -1)) {
                if (0 === strpos($className, $class)) {
                    return str_replace($class, $alias, $className);
                }
                continue;
            }

            if ($class == $className) {
                return $alias;
            }
        }

        return $className;
    }

    private function getHash($modelName, array $context = null)
    {
        $context = $context ?: [];

        $hash = md5(http_build_query($context));

        if (!array_key_exists($modelName, $this->definitionHashes)) {
            $this->definitionHashes[$modelName] = [];
        }

        if (false === ($index = array_search($hash, $this->definitionHashes[$modelName]))) {
            $this->definitionHashes[$modelName][] = $hash;
        }

        return array_search($hash, $this->definitionHashes[$modelName]);
    }

    public static function getPrimitiveType($type, ExtractionContextInterface $extractionContext = null)
    {
        if (!is_string($type)) {
            return null;
        }

        if (null === self::$typeResolver) {
            self::$typeResolver = new TypeResolver();
        }

        if ('generic' == $type) {
            $type = $extractionContext->getParameter('generic-template');
        }

        $result = self::$typeResolver->resolve($type);

        if ($result instanceof Collection) {
            return [
                'type' => 'generic',
                'class' => (string) $result->getFqsen(),
                'template' => (string) $result->getValueType(),
            ];
        }

        $primitiveType = [];

        $typeOfArray = str_replace('[]', '', $type);
        if ($typeOfArray != $type) {
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
            'dateTime' => ['type' => 'string', 'format' => 'date-time'],
            'password' => ['type' => 'string', 'format' => 'password'],
            'array' => ['type' => 'array'],
        ];

        if (array_key_exists($type, $types)) {
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
