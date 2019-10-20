<?php namespace Draw\Bundle\PostOfficeBundle\DependencyInjection;

use Draw\Bundle\PostOfficeBundle\Email\EmailWriterInterface;
use Draw\Bundle\PostOfficeBundle\Listener\EmailEventListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $emailEventListener = $container->getDefinition(EmailEventListener::class);

        $writers = [];
        foreach ($container->findTaggedServiceIds(EmailWriterInterface::class) as $id => $tags) {
            $writers[$id] = new Reference($id);
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            $reflectionClass = new \ReflectionClass($class);
            foreach ($reflectionClass->getMethod('getForEmails')->invoke(null) as $methodName => $priority) {
                if (is_int($methodName)) {
                    $methodName = $priority;
                    $priority = 0;
                }
                $emailType = $reflectionClass->getMethod($methodName)->getParameters()[0]->getClass()->name;
                $emailEventListener
                    ->addMethodCall('addWriter', [$emailType, $id, $methodName, $priority]);
            }
        }

        $emailEventListener
            ->setArgument(
                '$serviceLocator',
                ServiceLocatorTagPass::register($container, $writers)
            );
    }
}