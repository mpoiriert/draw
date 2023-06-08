<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection\Compiler;

use Draw\Bundle\SonataExtraBundle\Builder\EventDispatcherFormContractor;
use Draw\Bundle\SonataExtraBundle\FieldDescriptionFactory\SubClassFieldDescriptionFactory;
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

        $container->setDefinition(
            'draw.sonata.admin.field_description_factory.orm',
            new Definition(SubClassFieldDescriptionFactory::class)
        )
            ->setDecoratedService('sonata.admin.field_description_factory.orm', 'sonata.admin.field_description_factory.orm.inner')
            ->setArgument('$decorated', new Reference('sonata.admin.field_description_factory.orm.inner'))
            ->setAutoconfigured(true)
            ->setAutowired(true);
    }
}
