<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

trait IntegrationTrait
{
    protected function isConfigEnabled(ContainerBuilder $container, array $config): bool
    {
        if (!\array_key_exists('enabled', $config)) {
            throw new InvalidArgumentException("The config array has no 'enabled' key.");
        }

        return (bool) $container->getParameterBag()->resolveValue($config['enabled']);
    }

    protected function renameDefinitions(
        ContainerBuilder $container,
        string $classOrNamespace,
        string $namePrefix
    ): void {
        if (class_exists($classOrNamespace, true)) {
            if (!$container->hasDefinition($classOrNamespace)) {
                return;
            }
            $definition = $container->getDefinition($classOrNamespace);
            if (null === $definition->getClass()) {
                $definition->setClass($classOrNamespace);
            }

            $container->removeDefinition($classOrNamespace);
            $container->setDefinition($namePrefix, $definition);
            $container->setAlias($classOrNamespace, $namePrefix);

            return;
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            if (0 !== strpos($id, $classOrNamespace)) {
                continue;
            }

            if (null === $definition->getClass()) {
                $definition->setClass($id);
            }

            $newDefinitionId = $this->serviceIdClassToNameConvention(str_replace(
                $classOrNamespace,
                $namePrefix,
                $id
            ));

            $container->removeDefinition($id);
            $container->setDefinition(
                $newDefinitionId,
                $definition
            );

            $container->setAlias(
                $id,
                $newDefinitionId
            );
        }
    }

    private function removeDefinitions(ContainerBuilder $container, array $ids): void
    {
        foreach ($ids as $id) {
            $container->removeDefinition($id);
        }
    }

    protected function serviceIdClassToNameConvention(string $input): string
    {
        $originalInput = $input;
        $input = str_replace('\\', '.', $input);
        $input = preg_replace('~(?<=\\w)([A-Z])~u', '_$1', $input);

        if (null === $input) {
            throw new RuntimeException(sprintf('preg_replace returned null for value "%s"', $originalInput));
        }

        return mb_strtolower($input);
    }
}
