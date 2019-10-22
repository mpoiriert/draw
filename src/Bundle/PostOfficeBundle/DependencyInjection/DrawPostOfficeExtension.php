<?php namespace Draw\Bundle\PostOfficeBundle\DependencyInjection;

use Draw\Bundle\PostOfficeBundle\Email\DefaultFromEmailWriter;
use Draw\Bundle\PostOfficeBundle\Email\EmailWriterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\NamedAddress;

class DrawPostOfficeExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $container
            ->registerForAutoconfiguration(EmailWriterInterface::class)
            ->addTag(EmailWriterInterface::class);

        // This is to remove singly implemented aliases
        $container->removeAlias(EmailWriterInterface::class);

        $container->setParameter(
            'draw_post_office.default_communication_locale',
            $config['default_communication_locale']
        );

        $this->configureDefaultFrom($config['default_from'], $container);
    }

    private function configureDefaultFrom($config, ContainerBuilder $container)
    {
        if(!$config['enabled']) {
            $container->removeDefinition(DefaultFromEmailWriter::class);
            return;
        }

        $container->getDefinition(DefaultFromEmailWriter::class)
            ->setArgument('$defaultFrom', new Reference('draw_post_office.default_from'));

        if (!empty($config['name'])) {
            $definition = (new Definition(NamedAddress::class))
                ->setArguments([$config['email'], $config['name']]);
        } else {
            $definition = (new Definition(Address::class))
                ->setArguments([$config['email']]);
        }

        $container->setDefinition('draw_post_office.default_from', $definition);
    }
}