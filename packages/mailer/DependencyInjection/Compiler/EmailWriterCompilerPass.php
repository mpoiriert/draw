<?php

namespace Draw\Component\Mailer\DependencyInjection\Compiler;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Core\Reflection\ReflectionExtractor;
use Draw\Component\Mailer\EmailComposer;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class EmailWriterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $emailWriterListenerDefinition = $container->findDefinition(EmailComposer::class);
        } catch (ServiceNotFoundException) {
            return;
        }

        $writers = [];
        foreach ($container->findTaggedServiceIds(EmailWriterInterface::class) as $id => $tags) {
            $writers[$id] = new Reference($id);
            $definition = $container->getDefinition($id);
            $forEmails = ReflectionAccessor::callMethod($class = $definition->getClass(), 'getForEmails');
            foreach ($forEmails as $methodName => $priority) {
                if (\is_int($methodName)) {
                    $methodName = $priority;
                    $priority = 0;
                }

                $emailTypes = ReflectionExtractor::getClasses(
                    (new \ReflectionMethod($class, $methodName))->getParameters()[0]->getType()
                );

                foreach ($emailTypes as $emailType) {
                    $emailWriterListenerDefinition
                        ->addMethodCall('addWriter', [$emailType, $id, $methodName, $priority])
                    ;
                }
            }
        }

        $emailWriterListenerDefinition
            ->setArgument(
                '$serviceLocator',
                ServiceLocatorTagPass::register($container, $writers)
            )
        ;
    }
}
