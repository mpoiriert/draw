<?php namespace Draw\Bundle\CommandBundle\DependencyInjection;

use Draw\Bundle\CommandBundle\Command;
use Draw\Bundle\CommandBundle\CommandFactory;
use Draw\Bundle\CommandBundle\Sonata\Admin\ExecutionAdmin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

class DrawCommandExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->configureSonata($config['sonata'], $loader, $container);

        $definition = $container->getDefinition(CommandFactory::class);

        foreach ($config['commands'] as $configuration) {
            $commandDefinition = new Definition(Command::class);
            $commandDefinition->setArguments([$configuration]);
            $definition->addMethodCall(
                'addCommand',
                [
                    $commandDefinition
                ]
            );
        }
    }

    private function configureSonata(array $config, Loader\FileLoader $fileLoader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $fileLoader->load('sonata.xml');

        $container
            ->getDefinition(ExecutionAdmin::class)
            ->addTag(
                'sonata.admin',
                array_intersect_key(
                    $config,
                    array_flip(['group', 'icon', 'label', 'pager_type'])
                ) + ['manager_type' => 'orm']
            );
    }
}