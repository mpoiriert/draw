<?php

namespace Draw\Bundle\UserBundle\DependencyInjection\Compiler;

use Draw\Bundle\UserBundle\Security\User\EventDrivenUserChecker;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class UserCheckerDecoratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setDefinition(
            EventDrivenUserChecker::class,
            new Definition(EventDrivenUserChecker::class)
        )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setDecoratedService('security.user_checker', 'security.user_checker.inner')
            ->setArgument('$decoratedUserChecker', new Reference('security.user_checker.inner'));
    }
}
