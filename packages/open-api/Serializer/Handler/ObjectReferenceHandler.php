<?php

namespace Draw\Component\OpenApi\Serializer\Handler;

use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class ObjectReferenceHandler implements SubscribingHandlerInterface
{
    private ManagerRegistry $managerRegistry;

    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'ObjectReference',
                'method' => 'serializeObjectReference',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'ObjectReference',
                'method' => 'deserializeObjectReference',
            ],
        ];
    }

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function serializeObjectReference(
        JsonSerializationVisitor $visitor,
        $value,
        array $type,
        Context $context
    ) {
        if (null === $value) {
            return null;
        }

        $class = $type['params'][0]['name'];
        $identifiers = $this->managerRegistry
            ->getManagerForClass($class)
            ->getMetadataFactory()
            ->getMetadataFor($class)
            ->getIdentifierValues($value);

        return current($identifiers);
    }

    public function deserializeObjectReference(
        JsonDeserializationVisitor $deserializationVisitor,
        $value,
        array $type,
        DeserializationContext $context
    ): ?object {
        if (null === $value) {
            return null;
        }

        $repository = $this->managerRegistry
            ->getRepository($type['params'][0]['name']);

        return $repository->find($value);
    }
}
