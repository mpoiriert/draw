<?php

namespace DependencyInjection;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\EventListener\PreObjectDeleteBatchEventEventListener;
use Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection\DrawSonataExtraExtensionTest;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @internal
 */
class DrawSonataExtraExtensionBatchDeleteCheckEnabledTest extends DrawSonataExtraExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            ...parent::getConfiguration(),
            'batch_delete_check' => [
                'enabled' => true,
            ],
        ];
    }

    public static function provideServiceDefinitionCases(): iterable
    {
        yield from parent::provideServiceDefinitionCases();
        yield [PreObjectDeleteBatchEventEventListener::class];
    }
}
