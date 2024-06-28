<?php

namespace Draw\Component\Log\DependencyInjection\Compiler;

use Draw\Component\Log\DecoratedLogger;
use Symfony\Component\DependencyInjection\Argument\BoundArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class LoggerDecoratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('monolog.logger')) {
            return;
        }

        foreach ($container->findTaggedServiceIds('logger.decorate') as $id => $tags) {
            if (!class_exists(DecoratedLogger::class)) {
                throw new \RuntimeException('You need to install draw/log to use the logger.decorate tag.');
            }

            foreach ($tags as $tag) {
                $decoratorDefinition = $this->createDecoratorLoggerDefinition($tag);

                $definition = $container->getDefinition($id);
                foreach ($definition->getArguments() as $index => $argument) {
                    if (!$this->isLoggerReference($argument)) {
                        continue;
                    }

                    $definition->replaceArgument(
                        $index,
                        (clone $decoratorDefinition)
                            ->setArgument('$logger', $argument)
                    );
                }

                $calls = $definition->getMethodCalls();
                foreach ($calls as $i => $call) {
                    foreach ($call[1] as $index => $argument) {
                        if (!$this->isLoggerReference($argument)) {
                            continue;
                        }

                        $calls[$i][1][$index] = (clone $decoratorDefinition)
                            ->setArgument('$logger', $argument);
                    }
                }
                $definition->setMethodCalls($calls);

                if (method_exists($definition, 'getBindings')) {
                    $bindings = $definition->getBindings();

                    if (isset($bindings['Psr\Log\LoggerInterface'])) {
                        $argument = new Reference($bindings['Psr\Log\LoggerInterface']->getValues()[0]);
                    } else {
                        $argument = new Reference('logger');
                    }

                    $binding = new BoundArgument(
                        (clone $decoratorDefinition)
                            ->setArgument('logger', $argument)
                    );

                    // Mark the binding as used already, to avoid reporting it as unused if the service does not use a
                    // logger injected through the LoggerInterface alias.
                    $values = $binding->getValues();
                    $values[2] = true;
                    $binding->setValues($values);

                    $bindings['Psr\Log\LoggerInterface'] = $binding;
                    $definition->setBindings($bindings);
                }
            }
        }
    }

    private function isLoggerReference($argument): bool
    {
        return
            $argument instanceof Reference
            && (
                'logger' === (string) $argument
                || str_starts_with((string) $argument, 'monolog.logger.')
            );
    }

    private function createDecoratorLoggerDefinition(array $tag): Definition
    {
        $message = $tag['message'] ?? '{message}';

        unset($tag['message']);

        return (new Definition(DecoratedLogger::class))
            ->setArgument('defaultContext', $tag)
            ->setArgument('decorateMessage', $message)
            ->addTag('kernel.reset', ['method' => 'reset']);
    }
}
