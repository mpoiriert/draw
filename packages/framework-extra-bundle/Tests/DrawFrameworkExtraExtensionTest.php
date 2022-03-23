<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\DrawFrameworkExtraExtension;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Draw\Contracts\Process\ProcessFactoryInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawFrameworkExtraExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawFrameworkExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield ['draw.process.factory'];
        yield [ProcessFactoryInterface::class, 'draw.process.factory'];
    }
}
