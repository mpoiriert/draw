<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\OpenApiIntegration;
use Draw\Component\OpenApi\Command\InstallSandboxCommand;
use Draw\Component\OpenApi\Controller\OpenApiController;
use Draw\Component\OpenApi\EventListener\DuplicateDefinitionAliasSchemaCleaner;
use Draw\Component\OpenApi\EventListener\RequestQueryParameterFetcherListener;
use Draw\Component\OpenApi\EventListener\RequestValidationListener;
use Draw\Component\OpenApi\EventListener\ResponseApiExceptionListener;
use Draw\Component\OpenApi\EventListener\ResponseSerializerListener;
use Draw\Component\OpenApi\EventListener\SchemaAddDefaultHeadersListener;
use Draw\Component\OpenApi\EventListener\SchemaCleanRequiredReadOnlyListener;
use Draw\Component\OpenApi\EventListener\SchemaSorterListener;
use Draw\Component\OpenApi\EventListener\SerializationControllerListener;
use Draw\Component\OpenApi\Exception\ConstraintViolationListException;
use Draw\Component\OpenApi\Extraction\Extractor\Caching\FileTrackingExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Caching\LoadFromCacheExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Caching\StoreInCacheExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\ChoiceConstraintExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\CountConstraintExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\LengthConstraintExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\NotBlankConstraintExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\NotNullConstraintExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Constraint\RangeConstraintExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Doctrine\InheritanceExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\PropertiesExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\ArrayHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\DoctrineObjectReferenceSchemaHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\DynamicObjectHandler;
use Draw\Component\OpenApi\Extraction\Extractor\JmsSerializer\TypeHandler\GenericTemplateHandler;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\HeaderAttributeExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\JsonRootSchemaExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\ParameterExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\QueryParameterAttributeExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\RequestBodyArgumentResolverExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\SerializationConfigurationExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\TagAttributeExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\VendorAttributeExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\VersioningRootSchemaExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\OpenApi\VersionLinkDocumentationExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\PhpDoc\OperationExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\PhpReflection\OperationResponseExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Symfony\RouteOperationExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\Symfony\RouterRootSchemaExtractor;
use Draw\Component\OpenApi\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Component\OpenApi\Naming\AliasesClassNamingFilter;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\OpenApi\Request\ParamConverter\DeserializeBodyParamConverter;
use Draw\Component\OpenApi\Request\ValueResolver\RequestBodyValueResolver;
use Draw\Component\OpenApi\SchemaBuilder\SchemaBuilderInterface;
use Draw\Component\OpenApi\SchemaBuilder\SymfonySchemaBuilder;
use Draw\Component\OpenApi\Serializer\Construction\DoctrineObjectConstructor;
use Draw\Component\OpenApi\Serializer\Construction\SimpleObjectConstructor;
use Draw\Component\OpenApi\Serializer\Handler\GenericSerializerHandler;
use Draw\Component\OpenApi\Serializer\Handler\ObjectReferenceHandler;
use Draw\Component\OpenApi\Serializer\Handler\OpenApiHandler;
use Draw\Component\OpenApi\Serializer\Subscriber\OpenApiSubscriber;
use Draw\Component\OpenApi\Versioning\RouteDefaultApiRouteVersionMatcher;
use Draw\Component\OpenApi\Versioning\RouteVersionMatcherInterface;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OpenApiIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new OpenApiIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'open_api';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'openApi' => [
                'enabled' => true,
                'caching_enabled' => true,
                'sandbox_url' => '/open-api/sandbox',
                'sort_schema' => false,
                'scoped' => [
                    'enabled' => false,
                    'scopes' => [],
                ],
                'versioning' => [
                    'enabled' => false,
                    'versions' => [
                    ],
                ],
                'definitionAliases' => [
                ],
                'classNamingFilters' => [
                    0 => AliasesClassNamingFilter::class,
                ],
                'headers' => [],
            ],
            'request' => [
                'enabled' => true,
                'queryParameter' => [
                    'enabled' => true,
                ],
                'bodyDeserialization' => [
                    'enabled' => true,
                ],
                'userRequestInterceptedException' => [
                    'enabled' => false,
                ],
            ],
            'response' => [
                'enabled' => true,
                'serializeNull' => true,
                'exceptionHandler' => [
                    'enabled' => true,
                    'useDefaultExceptionsStatusCodes' => true,
                    'exceptionsStatusCodes' => [],
                    'violationKey' => 'errors',
                ],
            ],
        ];
    }

    public function provideTestLoad(): iterable
    {
        yield [
            [
                [
                    'openApi' => [
                        'enabled' => true,
                        'caching_enabled' => true,
                        'sort_schema' => true,
                        'sandbox_url' => '/test/sandbox',
                        'schema' => [
                            'info' => [
                                'title' => 'test',
                            ],
                        ],
                        'versioning' => [
                            'enabled' => true,
                            'versions' => ['1', '2'],
                        ],
                        'headers' => [
                            [
                                'name' => 'X-Draw-Language',
                                'type' => 'string',
                                'default' => 'en',
                            ],
                        ],
                        'definitionAliases' => [
                            ['class' => 'App\\Entity\\', 'alias' => ''],
                            ['class' => 'App\\DTO\\', 'alias' => ''],
                        ],
                        'classNamingFilters' => [
                            AliasesClassNamingFilter::class,
                        ],
                    ],
                    'response' => [
                        'enabled' => true,
                        'serializeNull' => true,
                        'exceptionHandler' => [
                            'enabled' => true,
                            'exceptionsStatusCodes' => [
                                ['class' => \Exception::class, 'code' => 100],
                            ],
                            'useDefaultExceptionsStatusCodes' => true,
                            'violationKey' => 'errors',
                        ],
                    ],
                    'request' => [
                        'enabled' => true,
                        'queryParameter' => [
                            'enabled' => true,
                        ],
                        'bodyDeserialization' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
            [
                new ServiceConfiguration(
                    'draw.open_api',
                    [OpenApi::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.schema_builder',
                    [SchemaBuilderInterface::class],
                    function (Definition $definition): void {
                        $this->assertSame(SymfonySchemaBuilder::class, $definition->getClass());
                    }
                ),
                new ServiceConfiguration(
                    'draw.open_api.param_converter.deserialize_body_param_converter',
                    [DeserializeBodyParamConverter::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.request.value_resolver.request_body_value_resolver',
                    [RequestBodyValueResolver::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.constraint.choice_constraint_extractor',
                    [ChoiceConstraintExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.constraint.count_constraint_extractor',
                    [CountConstraintExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.constraint.length_constraint_extractor',
                    [LengthConstraintExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.constraint.not_blank_constraint_extractor',
                    [NotBlankConstraintExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.constraint.not_null_constraint_extractor',
                    [NotNullConstraintExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.constraint.range_constraint_extractor',
                    [RangeConstraintExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.doctrine.inheritance_extractor',
                    [InheritanceExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.caching.file_tracking_extractor',
                    [FileTrackingExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.caching.load_from_cache_extractor',
                    [LoadFromCacheExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.caching.store_in_cache_extractor',
                    [StoreInCacheExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.jms_serializer.properties_extractor',
                    [PropertiesExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.jms_serializer.type_handler.array_handler',
                    [ArrayHandler::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.jms_serializer.type_handler.doctrine_object_reference_schema_handler',
                    [DoctrineObjectReferenceSchemaHandler::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.jms_serializer.type_handler.dynamic_object_handler',
                    [DynamicObjectHandler::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.jms_serializer.type_handler.generic_template_handler',
                    [GenericTemplateHandler::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.header_attribute_extractor',
                    [HeaderAttributeExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.json_root_schema_extractor',
                    [JsonRootSchemaExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.parameter_extractor',
                    [ParameterExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.query_parameter_attribute_extractor',
                    [QueryParameterAttributeExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.request_body_argument_resolver_extractor',
                    [RequestBodyArgumentResolverExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.serialization_configuration_extractor',
                    [SerializationConfigurationExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.tag_attribute_extractor',
                    [TagAttributeExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.vendor_attribute_extractor',
                    [VendorAttributeExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.versioning_root_schema_extractor',
                    [VersioningRootSchemaExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.open_api.version_link_documentation_extractor',
                    [VersionLinkDocumentationExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.php_doc.operation_extractor',
                    [OperationExtractor::class],
                    function (Definition $definition): void {
                        static::assertSame(
                            [
                                [
                                    'registerExceptionResponseCodes',
                                    [\Exception::class, 100],

                                ],
                                [
                                    'registerExceptionResponseCodes',
                                    [ConstraintViolationListException::class, 400],
                                ],
                                [
                                    'registerExceptionResponseCodes',
                                    [AccessDeniedException::class, 403],
                                ],
                            ],
                            $definition->getMethodCalls()
                        );
                    }
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.php_reflection.operation_response_extractor',
                    [OperationResponseExtractor::class],
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.symfony.route_operation_extractor',
                    [RouteOperationExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.symfony.router_root_schema_extractor',
                    [RouterRootSchemaExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.extractor.type_schema_extractor',
                    [TypeSchemaExtractor::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.jms_serializer.construction.doctrine_object_constructor',
                    [
                        'jms_serializer.object_constructor',
                        DoctrineObjectConstructor::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.open_api.jms_serializer.construction.simple_object_constructor',
                    [
                        'jms_serializer.unserialize_object_constructor',
                        SimpleObjectConstructor::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.open_api.jms_serializer.handler.generic_serializer_handler',
                    [GenericSerializerHandler::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.jms_serializer.handler.object_reference_handler',
                    [ObjectReferenceHandler::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.jms_serializer.handler.open_api_handler',
                    [OpenApiHandler::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.jms_serializer.subscriber.open_api_subscriber',
                    [OpenApiSubscriber::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.command.install_sandbox_command',
                    [InstallSandboxCommand::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.controller.open_api_controller',
                    [OpenApiController::class],
                    function (Definition $definition): void {
                        $this->assertSame(
                            [
                                'controller.service_arguments' => [[]],
                            ],
                            $definition->getTags()
                        );

                        $this->assertSame(
                            '/test/sandbox',
                            $definition->getArgument('$sandboxUrl')
                        );
                    }
                ),
                new ServiceConfiguration(
                    'draw.open_api.event_listener.schema_add_default_headers_listener',
                    [SchemaAddDefaultHeadersListener::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.event_listener.schema_sorter_listener',
                    [SchemaSorterListener::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.event_listener.schema_clean_required_read_only_listener',
                    [SchemaCleanRequiredReadOnlyListener::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.event_listener.serialization_controller_listener',
                    [SerializationControllerListener::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.naming.aliases_class_naming_filter',
                    [AliasesClassNamingFilter::class],
                    function (Definition $definition): void {
                        $this->assertSame(
                            [
                                ['class' => 'App\\Entity\\', 'alias' => ''],
                                ['class' => 'App\\DTO\\', 'alias' => ''],
                            ],
                            $definition->getArgument('$definitionAliases')
                        );
                    }
                ),
                new ServiceConfiguration(
                    'draw.open_api.event_listener.duplicate_definition_alias_schema_cleaner',
                    [DuplicateDefinitionAliasSchemaCleaner::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.versioning.route_default_api_route_version_matcher',
                    [RouteDefaultApiRouteVersionMatcher::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.event_listener.response_api_exception_listener',
                    [ResponseApiExceptionListener::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.event_listener.response_serializer_listener',
                    [ResponseSerializerListener::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.event_listener.request_query_parameter_fetcher_listener',
                    [RequestQueryParameterFetcherListener::class]
                ),
                new ServiceConfiguration(
                    'draw.open_api.event_listener.request_validation_listener',
                    [RequestValidationListener::class]
                ),
            ],
            [
                RouteDefaultApiRouteVersionMatcher::class => [
                    RouteVersionMatcherInterface::class,
                ],
                'jms_serializer.naming_strategy' => [
                    PropertyNamingStrategyInterface::class,
                ],
                'jms_serializer.metadata_factory' => [
                    MetadataFactoryInterface::class,
                ],
            ],
            [
                'draw_open_api.root_schema' => [
                    'info' => [
                        'title' => 'test',
                        'version' => '1.0',
                    ],
                    'swagger' => '2.0',
                ],
                'draw_open_api.response.serialize_null' => true,
                'draw_open_api.response.exception_status_codes' => [
                    \Exception::class => 100,
                    ConstraintViolationListException::class => 400,
                    AccessDeniedException::class => 403,
                ],
            ],
        ];
    }
}
