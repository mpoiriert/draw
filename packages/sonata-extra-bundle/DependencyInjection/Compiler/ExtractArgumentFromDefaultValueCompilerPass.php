<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection\Compiler;

use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExtractArgumentFromDefaultValueCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds(TaggedAdminInterface::ADMIN_TAG) as $id => $tags) {
            $definition = $container->getDefinition($id);

            $class = $container->getParameterBag()->resolveValue($definition->getClass());
            $reflectionClass = new \ReflectionClass($class);

            if (!$reflectionClass->hasMethod('__construct')) {
                continue;
            }

            $parameters = $reflectionClass->getMethod('__construct')->getParameters();
            foreach ($parameters as $index => $parameter) {
                if (isset($definition->getArguments()[$index])) {
                    continue;
                }

                if (!$parameter->isDefaultValueAvailable()) {
                    $defaultValue = null;
                } else {
                    $defaultValue = $parameter->getDefaultValue();
                }

                switch ($parameter->name) {
                    case 'code':
                    case 'class':
                    case 'baseControllerName':
                        $definition->setArgument($index, $defaultValue);
                        break;
                }
            }
        }
    }
}
