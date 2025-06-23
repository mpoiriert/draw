<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Block\MonitoringBlockService;
use Draw\Bundle\SonataExtraBundle\Controller\BatchAdminController;
use Draw\Bundle\SonataExtraBundle\Controller\KeepAliveController;
use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\InFilter;
use Draw\Bundle\SonataExtraBundle\Doctrine\Filter\RelativeDateTimeFilter;
use Draw\Bundle\SonataExtraBundle\EventListener\AdminMonitoringListener;
use Draw\Bundle\SonataExtraBundle\EventListener\ConfigureAdminControllerListener;
use Draw\Bundle\SonataExtraBundle\ExpressionLanguage\ExpressionLanguage;
use Draw\Bundle\SonataExtraBundle\Extension\BatchActionExtension;
use Draw\Bundle\SonataExtraBundle\Extension\DoctrineInheritanceExtension;
use Draw\Bundle\SonataExtraBundle\Extension\GridExtension;
use Draw\Bundle\SonataExtraBundle\Extension\ListFieldPriorityExtension;
use Draw\Bundle\SonataExtraBundle\Form\Extension\Core\Type\SingleLineDateTimeType;
use Draw\Bundle\SonataExtraBundle\Twig\EntityTwigExtension;
use Draw\Component\Tester\Test\DependencyInjection\ExtensionTestCase;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\DoctrineORMAdminBundle\DependencyInjection\SonataDoctrineORMAdminExtension;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @method DrawSonataExtraExtension getExtension()
 *
 * @internal
 */
class DrawSonataExtraExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'batch_delete_check' => [
                'enabled' => false,
            ],
        ];
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield [MonitoringBlockService::class];
        yield [BatchAdminController::class];
        yield [BatchActionExtension::class];
        yield [ExpressionLanguage::class];
        yield [GridExtension::class];
        yield [DoctrineInheritanceExtension::class];
        yield [InFilter::class];
        yield [ConfigureAdminControllerListener::class];
        yield [AdminMonitoringListener::class];
        yield [KeepAliveController::class];
        yield [RelativeDateTimeFilter::class];
        yield [ListFieldPriorityExtension::class];
        yield [SingleLineDateTimeType::class];
        yield [EntityTwigExtension::class];
    }

    public function testPrepend(): void
    {
        $containerBuilder = static::getContainerBuilder();
        $containerBuilder->registerExtension(new TwigExtension());
        $containerBuilder->registerExtension(new SonataDoctrineORMAdminExtension());
        $containerBuilder->registerExtension(new SonataAdminExtension());

        $this->getExtension()->prepend($containerBuilder);

        $result = $containerBuilder
            ->getExtensionConfig('twig')
        ;

        static::assertSame(
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
            ->getExtensionConfig('sonata_doctrine_orm_admin')
        ;

        static::assertSame(
            [
                [
                    'templates' => [
                        'types' => [
                            'show' => [
                                'actions' => '@DrawSonataExtra/CRUD/show_actions.html.twig',
                                'json' => '@DrawSonataExtra/CRUD/show_json.html.twig',
                                'list' => '@DrawSonataExtra/CRUD/show_list.html.twig',
                                'static' => '@DrawSonataExtra/CRUD/show_static.html.twig',
                                'url' => '@DrawSonataExtra/CRUD/show_url.html.twig',
                                'many_to_many' => '@DrawSonataExtra/CRUD/Association/show_many_to_many.html.twig',
                                'many_to_one' => '@DrawSonataExtra/CRUD/Association/show_many_to_one.html.twig',
                                'one_to_many' => '@DrawSonataExtra/CRUD/Association/show_one_to_many.html.twig',
                                'one_to_one' => '@DrawSonataExtra/CRUD/Association/show_one_to_one.html.twig',
                            ],
                            'list' => [
                                'list' => '@DrawSonataExtra/CRUD/list_list.html.twig',
                                'many_to_many' => '@DrawSonataExtra/CRUD/Association/list_many_to_many.html.twig',
                                'many_to_one' => '@DrawSonataExtra/CRUD/Association/list_many_to_one.html.twig',
                                'one_to_many' => '@DrawSonataExtra/CRUD/Association/list_one_to_many.html.twig',
                                'one_to_one' => '@DrawSonataExtra/CRUD/Association/list_one_to_one.html.twig',
                            ],
                        ],
                    ],
                ],
            ],
            $result
        );

        $result = $containerBuilder
            ->getExtensionConfig('sonata_admin')
        ;

        static::assertSame(
            [
                [
                    'assets' => [
                        'extra_javascripts' => [
                            'https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.2.0/json-viewer/jquery.json-viewer.js',
                            'bundles/drawsonataextra/js/json_viewer.js',
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
