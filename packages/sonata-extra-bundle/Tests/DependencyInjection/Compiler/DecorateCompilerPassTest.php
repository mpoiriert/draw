<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection\Compiler;

use Draw\Bundle\SonataExtraBundle\Builder\EventDispatcherFormContractor;
use Draw\Bundle\SonataExtraBundle\DependencyInjection\Compiler\DecoratesCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DecorateCompilerPassTest extends TestCase
{
    private DecoratesCompilerPass $compilerPass;

    private ContainerBuilder $containerBuilder;

    public function setUp(): void
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
    }
}
