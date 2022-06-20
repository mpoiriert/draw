<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection\Compiler;

use Draw\Bundle\SonataExtraBundle\Builder\EventDispatcherFormContractor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DecoratesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'draw.sonata.builder.orm_form',
            new Definition(EventDispatcherFormContractor::class)
        )
            ->setDecoratedService('sonata.admin.builder.orm_form', 'sonata.admin.builder.orm_form.inner')
            ->setArgument('$decoratedFormContractor', new Reference('sonata.admin.builder.orm_form.inner'))
            ->setAutoconfigured(true)
            ->setAutowired(true);
    }
}
