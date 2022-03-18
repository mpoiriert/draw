<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Block\AdminMonitoringBlockService;
use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\ExpressionLanguage\ExpressionLanguage;
use Draw\Bundle\SonataExtraBundle\Extension\GridExtension;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @method DrawSonataExtraExtension getExtension()
 */
class DrawSonataExtraExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [AdminMonitoringBlockService::class];
        yield [ExpressionLanguage::class];
        yield [GridExtension::class];
    }

    public function testPrepend(): void
    {
        $containerBuilder = static::getContainerBuilder();
        $containerBuilder->registerExtension(new TwigExtension());

        $this->getExtension()->prepend($containerBuilder);

        $result = $containerBuilder
            ->getExtensionConfig('twig');

        $this->assertSame(
            [
                [
                    'paths' => [
                        realpath(__DIR__.'/../../Resources/SonataAdminBundle/views') => 'SonataAdmin',
                    ],
                ],
            ],
            $result
        );
    }
}
