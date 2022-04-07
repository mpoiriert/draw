<?php

namespace Draw\Component\Tester;

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

trait MockBuilderTrait
{
    abstract public function getMockBuilder(string $className): MockBuilder;

    protected function createMockWithExtractMethods(string $originalClassName, array $methods): MockObject
    {
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(get_class_methods($originalClassName))
            ->addMethods($methods)
            ->getMock();
    }
}
