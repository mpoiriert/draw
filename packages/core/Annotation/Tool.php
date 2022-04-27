<?php

namespace Draw\Component\Core\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;

class Tool
{
    public static function ignoreNamespacesBaseOnClassExistence(string $class, array $namespaces)
    {
        if (class_exists(AnnotationReader::class)) {
            if (class_exists($class)) {
                return;
            }

            foreach ($namespaces as $namespace) {
                AnnotationReader::addGlobalIgnoredNamespace($namespace);
            }
        }
    }
}
