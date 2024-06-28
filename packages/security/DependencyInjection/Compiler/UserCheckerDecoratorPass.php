<?php

namespace Draw\Component\Security\DependencyInjection\Compiler;

use Draw\Component\Security\Core\User\EventDrivenUserChecker;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class UserCheckerDecoratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('security.user_checker')) {
            return;
        }

        $container->setDefinition(
            'draw.security.core.user.event_driven_user_checker',
            new Definition(EventDrivenUserChecker::class)
        )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setDecoratedService('security.user_checker', 'draw.security.core.user.event_driven_user_checker.inner')
            ->setArgument('$decoratedUserChecker', new Reference('draw.security.core.user.event_driven_user_checker.inner'));
    }
}
