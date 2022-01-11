<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Block\AdminMonitoringBlockService;
use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\ExpressionLanguage\ExpressionLanguage;
use Draw\Bundle\SonataExtraBundle\Listener\FixDepthMenuBuilderSubscriber;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawSonataExtraExtensionFixMenuDeptEnabledTest extends DrawSonataExtraExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'fix_menu_depth' => [
                'enabled' => true,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [AdminMonitoringBlockService::class];
        yield [ExpressionLanguage::class];
        yield [FixDepthMenuBuilderSubscriber::class];
    }
}
