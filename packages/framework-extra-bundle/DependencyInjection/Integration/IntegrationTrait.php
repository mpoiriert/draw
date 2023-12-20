<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

trait IntegrationTrait
{
    abstract public function getConfigSectionName(): string;

    protected function registerClasses(
        PhpFileLoader $loader,
        string $namespace,
        string $directory,
        array $exclude = [],
        ?Definition $prototype = null
    ): void {
        $prototype = $prototype ?: (new Definition())
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $loader->registerClasses(
            $prototype,
            $namespace,
            $directory,
            array_merge(
                $exclude,
                [
                    $directory.'/Attribute/',
                    $directory.'/Email/',
                    $directory.'/Entity/',
                    $directory.'/Event/',
                    $directory.'/Exception/',
                    $directory.'/Message/',
                    $directory.'/Resources/',
                    $directory.'/Stamp/',
                    $directory.'/Tests/',
                ]
            )
        );

        $container = ReflectionAccessor::getPropertyValue(
            $loader,
            'container',
        );

        \assert($container instanceof ContainerBuilder);

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->hasTag('container.excluded')) {
                $container->removeDefinition($id);
            }
        }
    }

    protected function assertHasExtension(
        ContainerBuilder $container,
        string $extensionName,
        ?string $exceptionMessage = null
    ): void {
        if ($container->hasExtension($extensionName)) {
            return;
        }

        throw new \Exception($exceptionMessage ?: sprintf('You must have the extension [%s] available to configuration [draw_framework_extra.%s]', $extensionName, $this->getConfigSectionName()));
    }

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
            if (!str_starts_with($id, $classOrNamespace)) {
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
            throw new \RuntimeException(sprintf('preg_replace returned null for value "%s"', $originalInput));
        }

        return mb_strtolower($input);
    }

    protected function arrayToArgumentsArray(array $arguments): array
    {
        $result = [];
        foreach ($arguments as $key => $value) {
            $result['$'.$key] = $value;
        }

        return $result;
    }
}
