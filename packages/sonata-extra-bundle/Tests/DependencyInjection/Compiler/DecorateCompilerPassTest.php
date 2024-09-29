<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection\Compiler;

use Draw\Bundle\SonataExtraBundle\Builder\EventDispatcherFormContractor;
use Draw\Bundle\SonataExtraBundle\DependencyInjection\Compiler\DecoratesCompilerPass;
use Draw\Bundle\SonataExtraBundle\FieldDescriptionFactory\SubClassFieldDescriptionFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class DecorateCompilerPassTest extends TestCase
{
    private DecoratesCompilerPass $compilerPass;

    private ContainerBuilder $containerBuilder;

    protected function setUp(): void
    {
        $this->compilerPass = new DecoratesCompilerPass();
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testProcess(): void
    {
        $this->compilerPass->process($this->containerBuilder);

        $definition = $this->containerBuilder->getDefinition('draw.sonata.builder.orm_form');

        static::assertSame(
            EventDispatcherFormContractor::class,
            $definition->getClass()
        );

        static::assertTrue($definition->isAutoconfigured());
        static::assertTrue($definition->isAutowired());

        static::assertSame(
            [
                'sonata.admin.builder.orm_form',
                'sonata.admin.builder.orm_form.inner',
                0,
            ],
            $definition->getDecoratedService()
        );

        static::assertSame(
            'sonata.admin.builder.orm_form.inner',
            (string) $definition->getArgument('$decoratedFormContractor')
        );

        $definition = $this->containerBuilder->getDefinition('draw.sonata.admin.field_description_factory.orm');

        static::assertSame(
            SubClassFieldDescriptionFactory::class,
            $definition->getClass()
        );

        static::assertTrue($definition->isAutoconfigured());
        static::assertTrue($definition->isAutowired());

        static::assertSame(
            [
                'sonata.admin.field_description_factory.orm',
                'sonata.admin.field_description_factory.orm.inner',
                0,
            ],
            $definition->getDecoratedService()
        );

        static::assertSame(
            'sonata.admin.field_description_factory.orm.inner',
            (string) $definition->getArgument('$decorated')
        );
    }
}
