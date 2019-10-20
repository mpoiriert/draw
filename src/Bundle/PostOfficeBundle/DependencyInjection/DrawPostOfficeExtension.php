<?php namespace Draw\Bundle\PostOfficeBundle\DependencyInjection;

use Draw\Bundle\PostOfficeBundle\Email\DefaultFromEmailWriter;
use Draw\Bundle\PostOfficeBundle\Email\EmailWriterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\NamedAddress;

class DrawPostOfficeExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(EmailWriterInterface::class)
            ->addTag(EmailWriterInterface::class);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->configureDefaultFrom($config['default_from'], $container);
    }

    private function configureDefaultFrom($config, ContainerBuilder $container)
    {
        if(!$config['enabled']) {
            $container->removeDefinition(DefaultFromEmailWriter::class);
            return;
        }

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