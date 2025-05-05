<?php

namespace Draw\Component\DataSynchronizer\Serializer;

use Draw\Component\DataSynchronizer\Serializer\Metadata\Driver\DoctrineTypeDriver;
use JMS\Serializer\Accessor\DefaultAccessorStrategy;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\ContextFactory\DefaultDeserializationContextFactory;
use JMS\Serializer\ContextFactory\DefaultSerializationContextFactory;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Expression\ExpressionEvaluatorInterface;
use JMS\Serializer\GraphNavigator\Factory\DeserializationGraphNavigatorFactory;
use JMS\Serializer\GraphNavigator\Factory\SerializationGraphNavigatorFactory;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\Visitor\Factory\JsonDeserializationVisitorFactory;
use JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory;
use Metadata\Driver\DriverInterface;
use Metadata\MetadataFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class SerializerFactory
{
    /**
     * @param iterable<SubscribingHandlerInterface> $handlers
     */
    public static function create(
        #[Autowire(service: 'draw.data_synchronizer.serializer.construction.default')]
        ObjectConstructorInterface $objectConstructor,
        #[Autowire(service: DoctrineTypeDriver::class)]
        DriverInterface $driver,
        #[TaggedIterator('draw.data_synchronizer.serializer.handler')]
        iterable $handlers,
        ?SerializationContextFactoryInterface $serializationContextFactory = null,
        ?DeserializationContextFactoryInterface $deserializationContextFactory = null,
        ?ExpressionEvaluatorInterface $expressionEvaluator = null,
    ): Serializer {
        $handlerRegistry = new HandlerRegistry();
        foreach ($handlers as $handler) {
            $handlerRegistry->registerSubscribingHandler($handler);
        }

        $serializationContextFactory ??= new DefaultSerializationContextFactory();
        $deserializationContextFactory ??= new DefaultDeserializationContextFactory();

        return new Serializer(
            factory: $metadataFactory = new MetadataFactory($driver),
            graphNavigators: [
                GraphNavigatorInterface::DIRECTION_SERIALIZATION => new SerializationGraphNavigatorFactory(
                    metadataFactory: $metadataFactory,
                    handlerRegistry: $handlerRegistry,
                    accessor: $accessor = new DefaultAccessorStrategy(),
                    dispatcher: new EventDispatcher(),
                    expressionEvaluator: $expressionEvaluator,
                ),
                GraphNavigatorInterface::DIRECTION_DESERIALIZATION => new DeserializationGraphNavigatorFactory(
                    metadataFactory: $metadataFactory,
                    handlerRegistry: $handlerRegistry,
                    objectConstructor: $objectConstructor,
                    accessor: $accessor,
                    dispatcher: null,
                    expressionEvaluator: $expressionEvaluator,
                ),
            ],
            serializationVisitors: [
                'json' => new JsonSerializationVisitorFactory(),
            ],
            deserializationVisitors: [
                'json' => new JsonDeserializationVisitorFactory(true),
            ],
            serializationContextFactory: $serializationContextFactory,
            deserializationContextFactory: $deserializationContextFactory,
        );
    }
}
