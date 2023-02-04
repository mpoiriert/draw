<?php

namespace Draw\Component\Core\Tests\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Draw\Component\Core\Annotation\Tool;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Core\Annotation\Tool
 */
class ToolTest extends TestCase
{
    public function testIgnoreNamespacesBaseOnClassExistenceClassExists(): void
    {
        $namespaces = [$namespace = uniqid('namespaces-')];
        Tool::ignoreNamespacesBaseOnClassExistence(self::class, $namespaces);

        static::assertArrayNotHasKey(
            $namespace,
            ReflectionAccessor::getPropertyValue(AnnotationReader::class, 'globalIgnoredNamespaces')
        );
    }

    public function testIgnoreNamespacesBaseOnClassExistenceClassDoesNotExits(): void
    {
        $namespaces = [$namespace1 = uniqid('namespaces-'), $namespace2 = uniqid('namespaces-')];
        Tool::ignoreNamespacesBaseOnClassExistence(uniqid('Class'), $namespaces);

        $ignoredNamespaces = ReflectionAccessor::getPropertyValue(AnnotationReader::class, 'globalIgnoredNamespaces');
        static::assertArrayHasKey(
            $namespace1,
            $ignoredNamespaces
        );

        static::assertArrayHasKey(
            $namespace2,
            $ignoredNamespaces
        );
    }
}
