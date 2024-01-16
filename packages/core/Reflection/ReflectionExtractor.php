<?php

namespace Draw\Component\Core\Reflection;

class ReflectionExtractor
{
    public static function getClasses(null|\ReflectionType $reflectionType): array
    {
        if (!$reflectionType) {
            return [];
        }

        if ($reflectionType instanceof \ReflectionIntersectionType) {
            throw new \InvalidArgumentException('Intersection type is not supported');
        }

        if ($reflectionType instanceof \ReflectionNamedType) {
            return [$reflectionType->getName()];
        }

        if ($reflectionType instanceof \ReflectionUnionType) {
            $classes = [];
            foreach ($reflectionType->getTypes() as $type) {
                $classes = array_merge($classes, static::getClasses($type));
            }

            return array_values(array_unique($classes));
        }

        throw new \InvalidArgumentException('Unknown type '.$reflectionType::class);
    }
}
