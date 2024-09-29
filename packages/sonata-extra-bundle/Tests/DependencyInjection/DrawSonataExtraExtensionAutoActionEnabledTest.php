<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\Extension\AutoActionExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @internal
 */
class DrawSonataExtraExtensionAutoActionEnabledTest extends DrawSonataExtraExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            ...parent::getConfiguration(),
            'auto_action' => [
                'enabled' => true,
            ],
        ];
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [AutoActionExtension::class];
    }
}
