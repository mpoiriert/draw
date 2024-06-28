<?php

namespace Draw\Component\DependencyInjection\Integration;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

trait ExtendableExtensionTrait
{
    /**
     * @var array<IntegrationInterface>
     */
    private array $integrations = [];

    abstract private function provideExtensionClasses(): array;

    /**
     * @return array<IntegrationInterface>
     */
    public function getIntegrations(): array
    {
        return $this->integrations;
    }

    private function registerDefaultIntegrations(): void
    {
        foreach ($this->provideExtensionClasses() as $extensionClass) {
            if (!class_exists($extensionClass)) {
                continue;
            }

            $integration = new $extensionClass();

            if (!$integration instanceof IntegrationInterface) {
                throw new \RuntimeException(sprintf('The class "%s" must implement "%s".', $extensionClass, IntegrationInterface::class));
            }

            $this->integrations[] = $integration;
        }
    }

    /**
     * @return array Parsed configuration
     */
    private function loadIntegrations(array $configs, ContainerBuilder $container): array
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        foreach ($this->integrations as $integration) {
            $container->addObjectResource($integration);
        }

        $loader = new PhpFileLoader($container, new FileLocator([]));

        foreach ($this->integrations as $integration) {
            if ($this->isConfigEnabled($container, $config[$integration->getConfigSectionName()])) {
                $integration->load($config[$integration->getConfigSectionName()], $loader, $container);
            }
        }

        return $config;
    }

    private function prependIntegrations(ContainerBuilder $container, string $mainExtension): void
    {
        $configs = $container->getExtensionConfig($mainExtension);

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        foreach ($this->integrations as $integration) {
            if (!$integration instanceof PrependIntegrationInterface) {
                continue;
            }

            $integrationConfiguration = $config[$integration->getConfigSectionName()];

            if ($this->isConfigEnabled($container, $integrationConfiguration)) {
                $integration->prepend($container, $integrationConfiguration);
            }
        }
    }
}
