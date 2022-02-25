<?php

namespace Draw\Bundle\CommandBundle\DependencyInjection;

use Draw\Bundle\CommandBundle\Authentication\Listener\CommandLineAuthenticatorListener;
use Draw\Bundle\CommandBundle\Authentication\SystemAuthenticatorInterface;
use Draw\Bundle\CommandBundle\CommandRegistry;
use Draw\Bundle\CommandBundle\Entity\Execution;
use Draw\Bundle\CommandBundle\Listener\CommandFlowListener;
use Draw\Bundle\CommandBundle\Model\Command;
use Draw\Bundle\CommandBundle\Sonata\Admin\ExecutionAdmin;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class DrawCommandExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->configureDoctrine($config['doctrine'], $container);
        $this->configureSonata($config['sonata'], $loader, $container);
        $this->configureAuthentication($config['authentication'], $loader, $container);

        $definition = $container->getDefinition(CommandRegistry::class);

        foreach ($config['commands'] as $configuration) {
            $commandDefinition = new Definition(Command::class);
            $commandDefinition->setArguments([$configuration]);
            $definition->addMethodCall(
                'setCommand',
                [
                    $commandDefinition,
                ]
            );
        }
    }

    private function configureDoctrine(array $config, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            $container->removeDefinition(CommandFlowListener::class);

            return;
        }
    }

    private function configureAuthentication(array $config, Loader\FileLoader $fileLoader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $fileLoader->load('authentication.xml');

        $container
            ->getDefinition(CommandLineAuthenticatorListener::class)
            ->setArgument('$systemAutoLogin', $config['system_auto_login']);

        $container->setAlias(SystemAuthenticatorInterface::class, $config['system_authentication_service']);
    }

    private function configureSonata(array $config, Loader\FileLoader $fileLoader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $fileLoader->load('sonata.xml');

        $container->getDefinition(ExecutionAdmin::class)
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArguments([null, Execution::class, $config['controller_class']])
            ->addTag(
                'sonata.admin',
                array_intersect_key(
                    $config,
                    array_flip(['group', 'icon', 'label', 'pager_type'])
                ) + ['manager_type' => 'orm']
            );
    }
}
