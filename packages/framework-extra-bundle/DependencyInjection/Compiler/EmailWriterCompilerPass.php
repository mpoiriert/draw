<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EmailWriterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('draw.mailer.email_writer_listener')) {
            return;
        }

        $emailWriterListenerDefinition = $container->getDefinition('draw.mailer.email_writer_listener');

        $writers = [];
        foreach ($container->findTaggedServiceIds(EmailWriterInterface::class) as $id => $tags) {
            $writers[$id] = new Reference($id);
            $definition = $container->getDefinition($id);
            $forEmails = ReflectionAccessor::callMethod($class = $definition->getClass(), 'getForEmails');
            foreach ($forEmails as $methodName => $priority) {
                if (is_int($methodName)) {
                    $methodName = $priority;
                    $priority = 0;
                }

                $emailType = (new ReflectionMethod($class, $methodName))->getParameters()[0]->getClass()->name;
                $emailWriterListenerDefinition
                    ->addMethodCall('addWriter', [$emailType, $id, $methodName, $priority]);
            }
        }

        $emailWriterListenerDefinition
            ->setArgument(
                '$serviceLocator',
                ServiceLocatorTagPass::register($container, $writers)
            );
    }
}
