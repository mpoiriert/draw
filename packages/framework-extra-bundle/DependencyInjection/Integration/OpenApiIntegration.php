<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Draw\Component\OpenApi\Configuration\Deserialization;
use Draw\Component\OpenApi\Configuration\Serialization;
use Draw\Component\OpenApi\Controller\OpenApiController;
use Draw\Component\OpenApi\EventListener\RequestQueryParameterFetcherListener;
use Draw\Component\OpenApi\EventListener\RequestValidationListener;
use Draw\Component\OpenApi\EventListener\ResponseApiExceptionListener;
use Draw\Component\OpenApi\EventListener\ResponseSerializerListener;
use Draw\Component\OpenApi\EventListener\SchemaAddDefaultHeadersListener;
use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Extraction\ExtractionContext;
use Draw\Component\OpenApi\Extraction\Extractor\Caching\LoadFromCacheExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Caching\StoreInCacheExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\BaseConstraintExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\ConstraintExtractionContext;
use Draw\Component\OpenApi\Extraction\Extractor\Doctrine\InheritanceExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\Event\PropertyExtractedEvent;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\TypeToSchemaHandlerInterface;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\VersionLinkDocumentationExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Naming\AliasesClassNamingFilter;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Request\ParamConverter\DeserializeBodyParamConverter;
use Draw\Component\OpenApi\SchemaBuilder\SchemaBuilderInterface;
use Draw\Component\OpenApi\SchemaBuilder\SymfonySchemaBuilder;
use Draw\Component\OpenApi\Serializer\Construction\DoctrineObjectConstructor;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Metadata\MetadataFactoryInterface;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OpenApiIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'open_api';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->configOpenApi($config['openApi'], $loader, $container);
        $this->configResponse($config['response'], $loader, $container);
        $this->configRequest($config['request'], $loader, $container);

        $this->removeDefinitions(
            $container,
            [
                Deserialization::class,
                Serialization::class,
                BaseConstraintExtractor::class,
                ExtractionContext::class,
                ConstraintExtractionContext::class,
                PropertyExtractedEvent::class,
            ]
        );

        $this->renameDefinitions(
            $container,
            OpenApi::class,
            'draw.open_api'
        );

        $this->renameDefinitions(
            $container,
            'Draw\\Component\\OpenApi\\Request\\ParamConverter\\',
            'draw.open_api.param_converter.'
        );

        $this->renameDefinitions(
            $container,
            'Draw\\Component\\OpenApi\\Extraction\\Extractor\\',
            'draw.open_api.extractor.'
        );

        $this->renameDefinitions(
            $container,
            'Draw\\Component\\OpenApi\\Serializer\\',
            'draw.open_api.jms_serializer.'
        );

        $this->renameDefinitions(
            $container,
            'Draw\\Component\\OpenApi\\',
            'draw.open_api.'
        );
    }

    private function configOpenApi(array $config, PhpFileLoader $loader, ContainerBuilder $container)
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $container->setAlias(
            PropertyNamingStrategyInterface::class,
            'jms_serializer.naming_strategy'
        );

        $container->setAlias(
            MetadataFactoryInterface::class,
            'jms_serializer.metadata_factory'
        );

        $container->setParameter('draw_open_api.root_schema', $config['schema']);

        $container
            ->registerForAutoconfiguration(ExtractorInterface::class)
            ->addTag(ExtractorInterface::class);

        $container
            ->registerForAutoconfiguration(TypeToSchemaHandlerInterface::class)
            ->addTag(TypeToSchemaHandlerInterface::class);

        $definition = (new Definition())
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $openApiComponentDir = dirname((new ReflectionClass(OpenApi::class))->getFileName());

        $exclude = [
            $openApiComponentDir.'/Event/',
            $openApiComponentDir.'/EventListener/{Request,Response}*',
            $openApiComponentDir.'/Exception/',
            $openApiComponentDir.'/Request/',
            $openApiComponentDir.'/Schema/',
            $openApiComponentDir.'/SchemaBuilder/',
            $openApiComponentDir.'/Tests/',
        ];

        if (!$config['caching_enabled']) {
            $exclude[] = $openApiComponentDir.'/Extraction/Extractor/Caching/';
        }

        $loader->registerClasses(
            $definition,
            'Draw\\Component\\OpenApi\\',
            $openApiComponentDir,
            $exclude
        );

        $loader->registerClasses(
            $definition->addTag('controller.service_arguments'),
            'Draw\\Component\\OpenApi\\Controller\\',
            $openApiComponentDir.'/Controller'
        );

        if ($this->isConfigEnabled($container, $config['versioning'])) {
            $container
                ->getDefinition(VersionLinkDocumentationExtractor::class)
                ->setArgument('$versions', $config['versioning']['versions']);
        } else {
            $container->removeDefinition(VersionLinkDocumentationExtractor::class);
        }

        if (!class_exists(DoctrineBundle::class)) {
            $container->removeDefinition(InheritanceExtractor::class);
        }

        $container
            ->setDefinition(
                'draw.open_api.schema_builder',
                new Definition(SymfonySchemaBuilder::class)
            )
            ->setAutowired(true)
            ->setAutoconfigured(true);

        if ($config['caching_enabled']) {
            $arguments = [
                '$debug' => new Parameter('kernel.debug'),
                '$cacheDirectory' => new Parameter('kernel.cache_dir'),
            ];

            $container
                ->getDefinition(LoadFromCacheExtractor::class)
                ->setArguments($arguments);

            $container
                ->getDefinition(StoreInCacheExtractor::class)
                ->setArguments($arguments);
        }

        $container
            ->setAlias(
                SchemaBuilderInterface::class,
                'draw.open_api.schema_builder'
            );

        $container
            ->getDefinition(OpenApi::class)
            ->setArgument('$extractors', new TaggedIteratorArgument(ExtractorInterface::class))
            ->addMethodCall('setCleanOnDump', [$config['cleanOnDump']]);

        $container
            ->getDefinition(OpenApiController::class)
            ->setArgument('$sandboxUrl', $config['sandbox_url']);

        $container
            ->getDefinition(PropertiesExtractor::class)
            ->setArgument('$typeToSchemaHandlers', new TaggedIteratorArgument(TypeToSchemaHandlerInterface::class));

        if (!$config['headers']) {
            $container->removeDefinition(SchemaAddDefaultHeadersListener::class);
        } else {
            $container
                ->getDefinition(SchemaAddDefaultHeadersListener::class)
                ->setArgument('$headers', $config['headers']);
        }

        if (!$config['definitionAliases']) {
            $container->removeDefinition(AliasesClassNamingFilter::class);
        } else {
            $config['classNamingFilters'][] = AliasesClassNamingFilter::class;
            $container
                ->getDefinition(AliasesClassNamingFilter::class)
                ->setArgument('$definitionAliases', $config['definitionAliases']);
        }

        $container
            ->getDefinition(DoctrineObjectConstructor::class)
            ->setArgument(
                '$fallbackConstructor',
                new Reference('jms_serializer.unserialize_object_constructor')
            );

        $namingFilterServices = [];
        foreach (array_unique($config['classNamingFilters']) as $serviceName) {
            $namingFilterServices[] = new Reference($serviceName);
        }

        $container
            ->getDefinition(TypeSchemaExtractor::class)
            ->setArgument('$classNamingFilters', $namingFilterServices);

        $container->setAlias(
            'jms_serializer.object_constructor',
            'draw.open_api.jms_serializer.construction.doctrine_object_constructor'
        );

        $container->setAlias(
            'jms_serializer.unserialize_object_constructor',
            'draw.open_api.jms_serializer.construction.simple_object_constructor'
        );
    }

    private function configResponse(array $config, PhpFileLoader $loader, ContainerBuilder $container)
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $openApiComponentDir = dirname((new ReflectionClass(OpenApi::class))->getFileName());

        $definition = (new Definition())
            ->setAutoconfigured(true)
            ->setAutowired(true);

        $loader->registerClasses(
            $definition,
            'Draw\\Component\\OpenApi\\EventListener\\',
            $openApiComponentDir.'/EventListener/{Response}*',
        );

        $container->setParameter('draw_open_api.response.serialize_null', $config['serializeNull']);

        $container
            ->getDefinition(ResponseSerializerListener::class)
            ->setArgument('$serializeNull', new Parameter('draw_open_api.response.serialize_null'));

        $this->configResponseExceptionHandler($config['exceptionHandler'], $loader, $container);
    }

    private function configResponseExceptionHandler(array $config, PhpFileLoader $loader, ContainerBuilder $container)
    {
        if (!$this->isConfigEnabled($container, $config)) {
            $container->removeDefinition(ResponseApiExceptionListener::class);

            return;
        }

        $codes = [];
        foreach ($config['exceptionsStatusCodes'] as $exceptionsStatusCodes) {
            $codes[$exceptionsStatusCodes['class']] = $exceptionsStatusCodes['code'];
        }

        if ($config['useDefaultExceptionsStatusCodes']) {
            $codes[ConstraintViolationListException::class] = 400;
            $codes[AccessDeniedException::class] = 403;
        }

        $container->setParameter('draw_open_api.response.exception_status_codes', $codes);

        $container->getDefinition(ResponseApiExceptionListener::class)
            ->setArgument(
                '$debug',
                new Parameter('kernel.debug')
            )
            ->setArgument(
                '$errorCodes',
                $codes
            )
            ->setArgument(
                '$violationKey',
                $config['violationKey']
            )
            ->setArgument(
                '$omitConstraintInvalidValue',
                $config['omitConstraintInvalidValue']
            );
    }

    private function configRequest(array $config, PhpFileLoader $loader, ContainerBuilder $container)
    {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $openApiComponentDir = dirname((new ReflectionClass(OpenApi::class))->getFileName());

        $definition = (new Definition())
            ->setAutoconfigured(true)
            ->setAutowired(true);

        $loader->registerClasses(
            $definition,
            'Draw\\Component\\OpenApi\\EventListener\\',
            $openApiComponentDir.'/EventListener/{Request}*',
        );

        $loader->registerClasses(
            $definition,
            'Draw\\Component\\OpenApi\\Request\\',
            $openApiComponentDir.'/Request',
        );

        $container
            ->getDefinition(RequestValidationListener::class)
            ->setArgument(
                '$prefixes',
                $config['validation']['pathPrefixes'] ??
                ['query' => '$.query', 'body' => '$.body']
            );

        if (!$config['queryParameter']['enabled']) {
            $container->removeDefinition(RequestQueryParameterFetcherListener::class);
        }

        if (!$config['bodyDeserialization']['enabled']) {
            $container->removeDefinition(DeserializeBodyParamConverter::class);
        } else {
            $container->getDefinition(DeserializeBodyParamConverter::class)
                ->addTag('request.param_converter', ['converter' => 'draw_open_api.request_body']);
        }
    }
}
