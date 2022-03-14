<?php

namespace Draw\Bundle\ApplicationBundle\Tests\DependencyInjection;

use Draw\Bundle\ApplicationBundle\DependencyInjection\DrawApplicationExtension;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawApplicationExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawApplicationExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        return [];
    }
}
