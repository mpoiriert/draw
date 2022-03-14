<?php

namespace Draw\Bundle\ApplicationBundle\Tests\DependencyInjection;

use Draw\Bundle\ApplicationBundle\Configuration\Sonata\Admin\ConfigAdmin;
use Draw\Bundle\ApplicationBundle\DependencyInjection\DrawApplicationExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawApplicationExtensionConfigurationAndSonataEnabledTest extends DrawApplicationExtensionConfigurationEnabledTest
{
    public function createExtension(): Extension
    {
        return new DrawApplicationExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'configuration' => [
                'sonata' => true,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [ConfigAdmin::class];
    }
}
