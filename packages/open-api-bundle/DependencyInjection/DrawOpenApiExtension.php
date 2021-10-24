<?php

namespace Draw\Bundle\OpenApiBundle\DependencyInjection;

use Draw\Bundle\OpenApiBundle\Exception\ConstraintViolationListException;
use Draw\Bundle\OpenApiBundle\Request\Listener\QueryParameterFetcherSubscriber;
use Draw\Bundle\OpenApiBundle\Request\Listener\ValidationSubscriber;
use Draw\Bundle\OpenApiBundle\Request\RequestBodyParamConverter;
use Draw\Bundle\OpenApiBundle\Response\Listener\ApiExceptionSubscriber;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\TypeToSchemaHandlerInterface;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Draw\Component\OpenApi\Naming\AliasesClassNamingFilter;
use Draw\Component\OpenApi\OpenApi;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DrawOpenApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $fileLocator = new FileLocator(__DIR__.'/../Resources/config');
        $loader = new XmlFileLoader($container, $fileLocator);

        $loader->load('services.xml');

        $this->configOpenApi($config['openApi'], $loader, $container);
        $this->configDoctrine($config['doctrine'], $loader, $container);
        $this->configResponse($config['response'], $loader, $container);
        $this->configRequest($config['request'], $loader, $container);
    }

    private function configOpenApi(array $config, LoaderInterface $loader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $container->setParameter('draw_open_api.root_schema', $config['schema']);
        $container->setParameter(
            'draw_open_api.component_dir',
            dirname((new ReflectionClass(OpenApi::class))->getFileName())
        );

        $container
            ->registerForAutoconfiguration(ExtractorInterface::class)
            ->addTag(ExtractorInterface::class);

        $container
            ->registerForAutoconfiguration(TypeToSchemaHandlerInterface::class)
            ->addTag(TypeToSchemaHandlerInterface::class);

        $loader->load('open-api.xml');
        $loader->load('jms-serializer.xml');

        $container
            ->getDefinition(OpenApi::class)
            ->addMethodCall('setCleanOnDump', [$config['cleanOnDump']]);

        $definition = $container->getDefinition(AliasesClassNamingFilter::class);
        $definition->setArgument(0, $config['definitionAliases']);

        $namingFilterServices = [];
        foreach ($config['classNamingFilters'] as $serviceName) {
            $namingFilterServices[] = new Reference($serviceName);
        }
        $container
            ->getDefinition(TypeSchemaExtractor::class)
            ->setArgument('$classNamingFilters', $namingFilterServices);
    }

    private function configResponse(array $config, LoaderInterface $loader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $container->setParameter('draw_open_api.response.serialize_null', $config['serializeNull']);
        $loader->load('response.xml');

        $this->configResponseExceptionHandler($config['exceptionHandler'], $loader, $container);
    }

    private function configResponseExceptionHandler(array $config, LoaderInterface $loader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            $container->removeDefinition(ApiExceptionSubscriber::class);

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

        $container->getDefinition(ApiExceptionSubscriber::class)
            ->setArgument(
                '$errorCodes',
                $codes
            )
            ->setArgument(
                '$violationKey',
                $config['violationKey']
            )
            ->setArgument(
                '$ignoreConstraintInvalidValue',
                $config['ignoreConstraintInvalidValue']
            );
    }

    private function configRequest(array $config, LoaderInterface $loader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('request.xml');

        $container
            ->getDefinition(ValidationSubscriber::class)
            ->setArgument(
                '$prefixes',
                $config['validation']['pathPrefixes'] ??
                ['query' => '$.query', 'body' => '$.body']
            );

        if (!$config['queryParameter']['enabled']) {
            $container->removeDefinition(QueryParameterFetcherSubscriber::class);
        }

        if (!$config['bodyDeserialization']['enabled']) {
            $container->removeDefinition(RequestBodyParamConverter::class);
        }
    }

    private function configDoctrine(array $config, LoaderInterface $loader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('doctrine.xml');
    }
}
