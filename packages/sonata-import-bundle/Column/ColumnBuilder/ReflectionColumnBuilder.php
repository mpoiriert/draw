<?php

namespace Draw\Bundle\SonataImportBundle\Column\ColumnBuilder;

use Draw\Bundle\SonataImportBundle\Entity\Column;

class ReflectionColumnBuilder implements ColumnBuilderInterface
{
    public function extract(string $class, Column $column, array $samples): ?Column
    {
        $headerName = $column->getHeaderName();

        $reflectionClass = new \ReflectionClass($class);

        $method = 'set'.$headerName;

        if (!$reflectionClass->hasMethod($method)) {
            return null;
        }

        $reflectionMethod = $reflectionClass->getMethod($method);

        $parameters = $reflectionMethod->getParameters();

        // No parameter cannot be a proper setter
        if (0 === \count($parameters)) {
            return null;
        }

        $parameter = array_shift($parameters);
        // More than one parameter and default value is not available it's not a proper setter
        foreach ($parameters as $parameter) {
            if (!$parameter->isDefaultValueAvailable()) {
                return null;
            }
        }

        $columnInfo = (new Column())
            ->setMappedTo($headerName);

        $type = $parameter->getType();

        if ($type instanceof \ReflectionNamedType) {
            if ($this->isDate($type)) {
                $columnInfo->setIsDate(true);
            }

            return $columnInfo;
        }

        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $type) {
                if ($this->isDate($type)) {
                    $columnInfo->setIsDate(true);
                }
            }

            return $columnInfo;
        }

        return $columnInfo;
    }

    private function isDate(\ReflectionNamedType $type): bool
    {
        $class = $type->getName();
        if (class_exists($class) && is_subclass_of($class, \DateTimeInterface::class)) {
            return true;
        }

        return false;
    }
}
