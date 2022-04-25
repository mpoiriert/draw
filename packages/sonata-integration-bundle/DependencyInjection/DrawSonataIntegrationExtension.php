<?php

namespace Draw\Bundle\SonataIntegrationBundle\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Configuration\SonataAdminNodeConfiguration;
use Draw\Bundle\SonataIntegrationBundle\Configuration\Admin\ConfigAdmin;
use Draw\Bundle\SonataIntegrationBundle\Console\Admin\ExecutionAdmin;
use Draw\Bundle\SonataIntegrationBundle\Console\Command;
use Draw\Bundle\SonataIntegrationBundle\Console\CommandRegistry;
use Draw\Bundle\SonataIntegrationBundle\Messenger\Admin\MessengerMessageAdmin;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

class DrawSonataIntegrationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->configureConfiguration($config['configuration'], $loader, $container);
        $this->configureConsole($config['console'], $loader, $container);
        $this->configureMessenger($config['messenger'], $loader, $container);
    }

    private function configureConfiguration(
        array $config,
        Loader\FileLoader $fileLoader,
        ContainerBuilder $container
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $container
            ->setDefinition(
                ConfigAdmin::class,
                SonataAdminNodeConfiguration::configureFromConfiguration(
                    new Definition(ConfigAdmin::class),
                    $config['admin']
                )
            )
            ->addMethodCall(
                'setTranslationDomain',
                ['DrawConfigurationSonata']
            );
    }

    private function configureConsole(array $config, Loader\FileLoader $fileLoader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container
            ->setDefinition(
                ExecutionAdmin::class,
                SonataAdminNodeConfiguration::configureFromConfiguration(
                    new Definition(ExecutionAdmin::class),
                    $config['admin']
                )
            )
            ->setAutowired(true)
            ->setAutoconfigured(true);

        if (!$container->hasDefinition($config['admin']['controller_class'])) {
            $container->setDefinition(
                $config['admin']['controller_class'],
                (new Definition($config['admin']['controller_class']))
                    ->setAutoconfigured(true)
                    ->setAutowired(true)
                    ->addTag('controller.service_arguments')
            );
        }

        $definition = $container
            ->setDefinition(
                CommandRegistry::class,
                (new Definition(CommandRegistry::class))
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
            );

        foreach ($config['commands'] as $configuration) {
            $definition->addMethodCall(
                'setCommand',
                [
                    (new Definition(Command::class))
                        ->setArguments($this->arrayToArgumentsArray($configuration)),
                ]
            );
        }
    }

    private function configureMessenger(array $config, Loader\FileLoader $fileLoader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container
            ->setDefinition(
                MessengerMessageAdmin::class,
                SonataAdminNodeConfiguration::configureFromConfiguration(
                    new Definition(MessengerMessageAdmin::class),
                    $config['admin']
                )
            )
            ->addMethodCall(
                'setTemplate',
                ['show', '@DrawSonataIntegration/Messenger/Message/show.html.twig']
            )
            ->addMethodCall(
                'inject',
                [
                    '$queueNames' => $config['queue_names'],
                    '$envelopeFinder' => new Reference('draw.messenger.envelope_finder'),
                ]
            );
    }

    private function arrayToArgumentsArray(array $arguments): array
    {
        $result = [];
        foreach ($arguments as $key => $value) {
            $result['$'.$key] = $value;
        }

        return $result;
    }
}
