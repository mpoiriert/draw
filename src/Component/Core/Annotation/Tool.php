<?php

namespace Draw\Component\Core\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;

class Tool
{
    public static function ignoreNamespacesBaseOnClassExistence($class, array $namespaces)
    {
        if (!class_exists(AnnotationReader::class)) {
            return;
        }

        if (class_exists($class)) {
            return;
        }

        foreach ($namespaces as $namespace) {
            AnnotationReader::addGlobalIgnoredNamespace($namespace);
        }
    }
}
