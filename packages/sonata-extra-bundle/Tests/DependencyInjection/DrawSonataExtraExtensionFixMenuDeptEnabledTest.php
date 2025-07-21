<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\EventListener\FixDepthMenuBuilderListener;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @internal
 */
class DrawSonataExtraExtensionFixMenuDeptEnabledTest extends DrawSonataExtraExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            ...parent::getConfiguration(),
            'fix_menu_depth' => [
                'enabled' => true,
            ],
        ];
    }

    public static function provideServiceDefinitionCases(): iterable
    {
        yield from parent::provideServiceDefinitionCases();
        yield [FixDepthMenuBuilderListener::class];
    }
}
