<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Draw\Component\Core\Reflection\ReflectionAccessor;
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

                foreach ($this->getClasses((new \ReflectionMethod($class, $methodName))->getParameters()[0]) as $emailType) {
                    $emailWriterListenerDefinition
                        ->addMethodCall('addWriter', [$emailType, $id, $methodName, $priority]);
                }
            }
        }

        $emailWriterListenerDefinition
            ->setArgument(
                '$serviceLocator',
                ServiceLocatorTagPass::register($container, $writers)
            );
    }

    /**
     * Extract classes base on union and name type.
     *
     * @return array<class-string>
     */
    private function getClasses(\ReflectionParameter $reflectionParameter): array
    {
        $type = $reflectionParameter->getType();

        if ($type instanceof \ReflectionNamedType) {
            return [$type->getName()];
        }

        if ($type instanceof \ReflectionUnionType) {
            return array_map(
                fn (\ReflectionNamedType $type) => $type->getName(),
                $type->getTypes()
            );
        }

        throw new \InvalidArgumentException('Unable to extract classes from parameter. Only named type and union type are supported.');
    }
}
