<?php

namespace Draw\Component\OpenApi\Serializer\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class ObjectReferenceHandler implements SubscribingHandlerInterface
{
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

    public function __construct(
        private ?ManagerRegistry $ormManagerRegistry,
        private ?ManagerRegistry $odmManagerRegistry,
    ) {
    }

    public function serializeObjectReference(
        JsonSerializationVisitor $visitor,
        $value,
        array $type,
        Context $context,
    ) {
        if (null === $value) {
            return null;
        }

        $class = $type['params'][0]['name'];
        $identifiers = $this->getManagerForClass($class)
            ->getMetadataFactory()
            ->getMetadataFor($class)
            ->getIdentifierValues($value)
        ;

        return current($identifiers);
    }

    public function deserializeObjectReference(
        JsonDeserializationVisitor $deserializationVisitor,
        $value,
        array $type,
        DeserializationContext $context,
    ): ?object {
        if (null === $value) {
            return null;
        }

        return $this->getManagerForClass($type['params'][0]['name'])
            ->find($type['params'][0]['name'], $value)
        ;
    }

    private function getManagerForClass(string $class): ObjectManager
    {
        return $this->ormManagerRegistry?->getManagerForClass($class)
            ?? $this->odmManagerRegistry?->getManagerForClass($class)
            ?? throw new \RuntimeException('No object manager found for class '.$class);
    }
}
