<?php

namespace Draw\Bundle\SonataImportBundle\Column\Extractor;

use Draw\Bundle\SonataImportBundle\Column\BaseColumnExtractor;
use Draw\Bundle\SonataImportBundle\Entity\Column;

class SetterMethodReflectionColumnExtractor extends BaseColumnExtractor
{
    #[\Override]
    public function getOptions(Column $column, array $options): array
    {
        $class = $column->getImport()->getEntityClass();

        $reflectionClass = new \ReflectionClass($class);

        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (!$this->isValidMethod($reflectionMethod)) {
                continue;
            }

            $name = $reflectionMethod->getName();

            $options[] = lcfirst(substr($name, 3));
        }

        return $options;
    }

    #[\Override]
    public function extractDefaultValue(Column $column, array $samples): ?Column
    {
        $class = $column->getImport()->getEntityClass();

        $headerName = $column->getHeaderName();

        $reflectionClass = new \ReflectionClass($class);

        $method = 'set'.$headerName;

        if (!$reflectionClass->hasMethod($method)) {
            return null;
        }

        $reflectionMethod = $reflectionClass->getMethod($method);

        if (!$this->isValidMethod($reflectionMethod)) {
            return null;
        }

        $parameters = $reflectionMethod->getParameters();

        $parameter = array_shift($parameters);

        $columnInfo = (new Column())
            ->setMappedTo($headerName)
        ;

        if ($this->isDate($parameter)) {
            $columnInfo->setIsDate(true);
        }

        return $columnInfo;
    }

    private function isValidMethod(\ReflectionMethod $reflectionMethod): bool
    {
        $name = $reflectionMethod->getName();

        if (!str_starts_with($name, 'set')) {
            return false;
        }

        if ($reflectionMethod->isStatic()) {
            return false;
        }

        if (0 === \count($parameters = $reflectionMethod->getParameters())) {
            return false;
        }

        $parameter = array_shift($parameters);

        \assert($parameter instanceof \ReflectionParameter);

        $type = $parameter->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return false;
        }

        $name = $type->getName();

        if (!\in_array($name, ['int', 'float', 'string', 'bool', 'mixed'], true) && !$this->isDate($parameter)) {
            return false;
        }

        // More than one parameter and default value is not available it's not a proper setter
        foreach ($parameters as $parameter) {
            if (!$parameter->isDefaultValueAvailable()) {
                return false;
            }
        }

        return true;
    }

    private function isDate(\ReflectionParameter $reflectionParameter): bool
    {
        $type = $reflectionParameter->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return false;
        }

        $class = $type->getName();
        if (
            (class_exists($class) || interface_exists($class))
            && is_a($class, \DateTimeInterface::class, true)
        ) {
            return true;
        }

        return false;
    }
}
