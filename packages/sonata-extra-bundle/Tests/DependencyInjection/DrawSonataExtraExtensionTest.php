<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Block\AdminMonitoringBlockService;
use Draw\Bundle\SonataExtraBundle\Controller\BatchController;
use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\InFilter;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\RelativeDateTimeFilter;
use Draw\Bundle\SonataExtraBundle\ExpressionLanguage\ExpressionLanguage;
use Draw\Bundle\SonataExtraBundle\Extension\BatchActionExtension;
use Draw\Bundle\SonataExtraBundle\Extension\GridExtension;
use Draw\Bundle\SonataExtraBundle\Request\ParamConverter\AdminParamConverter;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\DoctrineORMAdminBundle\DependencyInjection\SonataDoctrineORMAdminExtension;
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
        yield [BatchController::class];
        yield [BatchActionExtension::class];
        yield [ExpressionLanguage::class];
        yield [GridExtension::class];
        yield [InFilter::class];
        yield [RelativeDateTimeFilter::class];
        yield [AdminParamConverter::class];
    }

    public function testPrepend(): void
    {
        $containerBuilder = static::getContainerBuilder();
        $containerBuilder->registerExtension(new TwigExtension());
        $containerBuilder->registerExtension(new SonataDoctrineORMAdminExtension());
        $containerBuilder->registerExtension(new SonataAdminExtension());

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

        $result = $containerBuilder
            ->getExtensionConfig('sonata_doctrine_orm_admin');

        $this->assertSame(
            [
                [
                    'templates' => [
                        'types' => [
                            'show' => [
                                'actions' => '@DrawSonataExtra/CRUD/show_actions.html.twig',
                                'json' => '@DrawSonataExtra/CRUD/show_json.html.twig',
                                'list' => '@DrawSonataExtra/CRUD/show_list.html.twig',
                                'static' => '@DrawSonataExtra/CRUD/show_static.html.twig',
                            ],
                            'list' => [
                                'list' => '@DrawSonataExtra/CRUD/list_list.html.twig',
                            ],
                        ],
                    ],
                ],
            ],
            $result
        );

        $result = $containerBuilder
            ->getExtensionConfig('sonata_admin');

        $this->assertSame(
            [
                [
                    'assets' => [
                        'extra_javascripts' => [
                            'https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.2.0/json-viewer/jquery.json-viewer.js',
                        ],
                        'extra_stylesheets' => [
                            'https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.2.0/json-viewer/jquery.json-viewer.css',
                        ],
                    ],
                ],
            ],
            $result
        );
    }
}
