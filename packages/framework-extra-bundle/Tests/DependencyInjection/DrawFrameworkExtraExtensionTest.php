<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\DrawFrameworkExtraExtension;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawFrameworkExtraExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawFrameworkExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'console' => [
                'enabled' => false,
            ],
            'messenger' => [
                'enabled' => false,
            ],
            'security' => [
                'enabled' => false,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        return [];
    }
}
