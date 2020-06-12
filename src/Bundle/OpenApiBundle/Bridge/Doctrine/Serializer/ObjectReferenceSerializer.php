<?php

namespace Draw\Bundle\OpenApiBundle\Bridge\Doctrine\Serializer;

use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class ObjectReferenceSerializer implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'ObjectReference',
                'method' => 'serializeReference',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'ObjectReference',
                'method' => 'deserializeReference',
            ],
        ];
    }

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function serializeReference(
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

    public function deserializeReference(
        JsonDeserializationVisitor $deserializationVisitor,
        $value,
        array $type,
        DeserializationContext $context
    ) {
        $repository = $this->managerRegistry
            ->getRepository($type['params'][0]['name']);

        if (!isset($type['params'][1])) {
            return $repository->find($value);
        }
    }
}
