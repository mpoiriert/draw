<?php

namespace Draw\Bundle\SonataImportBundle\DependencyInjection;

use Draw\Bundle\SonataImportBundle\Column\Bridge\KnpDoctrineBehaviors\Extractor\DoctrineTranslationColumnExtractor;
use Draw\Bundle\SonataImportBundle\Column\ColumnExtractorInterface;
use Draw\Bundle\SonataImportBundle\Import\Importer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DrawSonataImportExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $container
            ->registerForAutoconfiguration(ColumnExtractorInterface::class)
            ->addTag('draw.sonata_import.extractor')
        ;

        $container->setParameter('draw.sonata_import.classes', $mergedConfig['classes']);
        $container->setParameter('draw.sonata_import.skip_value', $mergedConfig['skip_value']);

        $container
            ->getDefinition(Importer::class)
            ->setArgument('$skipValue', $mergedConfig['skip_value'])
        ;

        $this->loadDoctrineTranslationHandler($mergedConfig['handlers']['doctrine_translation'], $container);
    }

    protected function loadDoctrineTranslationHandler(array $config, ContainerBuilder $container): void
    {
        if (!$this->isConfigEnabled($container, $config)) {
            $container->removeDefinition(DoctrineTranslationColumnExtractor::class);

            return;
        }

        $container
            ->getDefinition(DoctrineTranslationColumnExtractor::class)
            ->setArgument('$supportedLocales', $config['supported_locales'])
        ;
    }
}
