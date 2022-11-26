<?php

namespace Draw\Bundle\SonataExtraBundle\DependencyInjection\Compiler;

use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExtractArgumentFromDefaultValueCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // todo remove on next sonata admin major since parameter in constructor will not be supported
        foreach ($container->findTaggedServiceIds(TaggedAdminInterface::ADMIN_TAG) as $id => $tags) {
            $definition = $container->getDefinition($id);

            $class = $container->getParameterBag()->resolveValue($definition->getClass());
            $reflectionClass = new \ReflectionClass($class);

            if (!$reflectionClass->hasMethod('__construct')) {
                continue;
            }

            $parameters = $reflectionClass->getMethod('__construct')->getParameters();
            $allNull = true;
            $parametersToSet = [];
            foreach ($parameters as $index => $parameter) {
                if (isset($definition->getArguments()[$index])) {
                    continue;
                }

                if (!$parameter->isDefaultValueAvailable()) {
                    $defaultValue = null;
                } else {
                    $defaultValue = $parameter->getDefaultValue();
                }

                if (null !== $defaultValue) {
                    $allNull = false;
                }

                switch ($parameter->name) {
                    case 'code':
                    case 'class':
                    case 'baseControllerName':
                        $parametersToSet[$index] = $defaultValue;
                        break;
                }
            }

            if (!$allNull) {
                foreach ($parametersToSet as $index => $value) {
                    $definition->setArgument($index, $value);
                }
            }
        }
    }
}
