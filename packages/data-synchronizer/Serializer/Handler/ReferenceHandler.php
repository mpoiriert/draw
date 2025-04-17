<?php

namespace Draw\Component\DataSynchronizer\Serializer\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\DataSynchronizer\Metadata\EntitySynchronizationMetadataFactory;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AutoconfigureTag('draw.data_synchronizer.serializer.handler')]
class ReferenceHandler implements SubscribingHandlerInterface
{
    public function __construct(
        private EntitySynchronizationMetadataFactory $importMetadataReader,
        #[Autowire(service: 'draw.data_synchronizer.serializer.construction.default')]
        private ObjectConstructorInterface $objectConstructor,
    ) {
    }

    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'ExtractionReference',
                'method' => 'serializeExtractionReference',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'ExtractionReference',
                'method' => 'deserializeExtractionReference',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'ExtractionReferenceCollection',
                'method' => 'serializeExtractionReferenceCollection',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'ExtractionReferenceCollection',
                'method' => 'deserializeExtractionReferenceCollection',
            ],
        ];
    }

    public function serializeExtractionReference(
        SerializationVisitorInterface $visitor,
        $object,
        array $type,
        Context $context,
    ): array|\ArrayObject {
        $lookUpFields = $this->importMetadataReader
            ->getEntitySynchronizationMetadata($type['params'][1]['name'])
            ->lookUpFields
        ;

        $className = $object::class;
        $metadata = $this->requireMetadata($className, $context);
        $visitor->startVisitingObject($metadata, $object, $type);
        foreach ($lookUpFields as $field) {
            $visitor->visitProperty(
                $metadata->propertyMetadata[$field],
                $object->{'get'.ucfirst($field)}(),
            );
        }

        $result = $visitor->endVisitingObject($metadata, $object, $type);

        if (!empty($metadata->discriminatorMap)) {
            $result[$metadata->discriminatorFieldName] = $metadata->discriminatorValue;
        }

        return $result;
    }

    public function deserializeExtractionReference(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        DeserializationContext $context,
    ): ?object {
        if (2 !== (is_countable($type['params']) ? \count($type['params']) : 0)) {
            throw new \RuntimeException('Reference type expected to have 2 parameters, one property name, other class name');
        }

        if (!$data) {
            return null;
        }

        return $this->loadEntity(
            $type['params'][1]['name'],
            $data,
            $visitor,
            $context
        );
    }

    public function serializeExtractionReferenceCollection(
        SerializationVisitorInterface $visitor,
        $object,
        array $type,
    ): array|\ArrayObject {
        $lookUpFields = $this->importMetadataReader
            ->getEntitySynchronizationMetadata($type['params'][1]['name'])
            ->lookUpFields
        ;

        $result = [];
        foreach ($object as $subObject) {
            $lookupData = [];
            foreach ($lookUpFields as $field) {
                $lookupData[$field] = $subObject->{'get'.ucfirst($field)}();
            }
            $result[] = $lookupData;
        }

        return $result;
    }

    public function deserializeExtractionReferenceCollection(
        JsonDeserializationVisitor $visitor,
        $data,
        array $type,
        DeserializationContext $context,
    ): ArrayCollection {
        if (2 !== (is_countable($type['params']) ? \count($type['params']) : 0)) {
            throw new \RuntimeException('Reference type expected to have 2 parameters, one property name, other class name');
        }

        $property = $type['params'][0]['name'];
        $class = $type['params'][1]['name'];

        $currentObject = $visitor->getCurrentObject();

        try {
            $currentEntities = ReflectionAccessor::getPropertyValue($currentObject, $property);
        } catch (\Error $e) {
            if (!str_contains($e->getMessage(), 'initialization')) {
                throw $e;
            }

            $currentEntities = null;
        }

        if ($currentEntities instanceof Collection) {
            $currentEntities = $currentEntities->toArray();
        }

        ReflectionAccessor::setPropertyValue(
            $currentObject,
            $property,
            $currentEntities = new ArrayCollection($currentEntities ?? [])
        );

        $newEntities = new ArrayCollection();
        foreach ($data as $datum) {
            try {
                $newEntities->add($this->loadEntity($class, $datum, $visitor, $context));
            } catch (\Throwable $error) {
                $lookUpFields = $this->importMetadataReader
                    ->getEntitySynchronizationMetadata($class)
                    ->lookUpFields
                ;
                throw new \RuntimeException(\sprintf('Failed to deserialize [%s] with field : [%s] and data : [%s]', $class, implode(',', $lookUpFields), implode(', ', $datum)), 0, $error);
            }
        }

        foreach ($currentEntities as $entity) {
            if ($newEntities->contains($entity)) {
                continue;
            }
            $currentEntities->removeElement($entity);
        }

        foreach ($newEntities as $entity) {
            if ($currentEntities->contains($entity)) {
                continue;
            }
            $currentEntities->add($entity);
        }

        return $currentEntities;
    }

    private function loadEntity(
        string $className,
        array $data,
        DeserializationVisitorInterface $visitor,
        DeserializationContext $context,
    ): ?object {
        $metadata = $this->requireMetadata($className, $context);

        if (!empty($metadata->discriminatorMap)
            && $className === $metadata->discriminatorBaseClass
            && isset($data[$metadata->discriminatorFieldName])
        ) {
            $metadata = $this->requireMetadata(
                $metadata->discriminatorMap[$data[$metadata->discriminatorFieldName]],
                $context
            );
        }

        return $this->objectConstructor->construct(
            $visitor,
            $metadata,
            $data,
            [],
            $context
        );
    }

    private function requireMetadata(string $className, Context $context): ClassMetadata
    {
        $metadata = $context->getMetadataFactory()->getMetadataForClass($className);
        if (!$metadata instanceof ClassMetadata) {
            throw new \RuntimeException('No metadata for class '.$className);
        }

        return $metadata;
    }
}
