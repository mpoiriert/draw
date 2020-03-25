<?php namespace Draw\Bundle\OpenApiBundle\DependencyInjection;

use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\TypeToSchemaHandlerInterface;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\OpenApi;
use Draw\Bundle\OpenApiBundle\Listener\ResponseConverterSubscriber;
use Draw\Bundle\OpenApiBundle\Request\DeserializeBody;
use Draw\Bundle\OpenApiBundle\Request\RequestBodyParamConverter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DrawOpenApiExtension extends ConfigurableExtension
{
    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function loadInternal(array $config, ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(ExtractorInterface::class)
            ->addTag(ExtractorInterface::class);

        $container
            ->registerForAutoconfiguration(TypeToSchemaHandlerInterface::class)
            ->addTag(TypeToSchemaHandlerInterface::class);

        $container->setParameter("draw_open_api.root_schema", $config['schema']);
        $container->setParameter(
            'draw_open_api.component_dir',
            dirname((new \ReflectionClass(OpenApi::class))->getFileName())
        );

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new XmlFileLoader($container, $fileLocator);

        $loader->load('open-api.xml');

        $container
            ->getDefinition(OpenApi::class)
            ->addMethodCall('setCleanOnDump', [$config['cleanOnDump']]);

        $definition = $container->getDefinition(TypeSchemaExtractor::class);

        foreach ($config['definitionAliases'] as $alias) {
            $definition->addMethodCall(
                'registerDefinitionAlias',
                [$alias['class'], $alias['alias']]
            );
        }

        $this->configDoctrine($config['doctrine'], $loader, $container);

        if ($config['convertQueryParameterToAttribute']) {
            $loader->load('query_parameter_fetcher.xml');
        }

        if ($config['responseConverter']['enabled']) {
            $loader->load('response_converter.xml');
            $container
                ->getDefinition(ResponseConverterSubscriber::class)
                ->setArgument('$serializeNull', $config['responseConverter']['serializeNull']);
        }

        $container
            ->getDefinition(RequestBodyParamConverter::class)
            ->setArgument(
                '$defaultConfiguration',
                new Definition(
                    DeserializeBody::class,
                    [$config['requestBodyParamConverter']['defaultDeserializationConfiguration']]
                )
            );
    }

    private function configDoctrine(array $config, LoaderInterface $loader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('doctrine.xml');
    }
}