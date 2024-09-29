<?php

namespace DependencyInjection;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\Notifier\Channel\SonataChannel;
use Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection\DrawSonataExtraExtensionTest;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @internal
 */
class DrawSonataExtraExtensionNotifierEnabledTest extends DrawSonataExtraExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            ...parent::getConfiguration(),
            'notifier' => [
                'enabled' => true,
            ],
        ];
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [SonataChannel::class];
    }
}
