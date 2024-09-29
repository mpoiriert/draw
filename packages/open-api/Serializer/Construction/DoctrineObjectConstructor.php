<?php

namespace Draw\Component\OpenApi\Serializer\Construction;

use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\InvalidArgumentException;
use JMS\Serializer\Exception\ObjectConstructionException;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Metadata\MetadataFactoryInterface;

/**
 * todo check if this required.
 */
class DoctrineObjectConstructor implements ObjectConstructorInterface
{
    final public const ON_MISSING_NULL = 'null';
    final public const ON_MISSING_EXCEPTION = 'exception';
    final public const ON_MISSING_FALLBACK = 'fallback';

    public function __construct(
        private ManagerRegistry $managerRegistry,
        private ObjectConstructorInterface $fallbackConstructor,
        private MetadataFactoryInterface $metadataFactory,
    ) {
    }

    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context,
    ): ?object {
        // Locate possible ObjectManager
        $objectManager = $this->managerRegistry->getManagerForClass($metadata->name);

        if (!$objectManager) {
            // No ObjectManager found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        // If the object is not found we relay on the fallback constructor
        if (null === ($object = $this->loadObject($metadata->name, $data, $context, $context->getCurrentPath()))) {
            $constructionFallbackStrategy = null;
            if ($context->hasAttribute('constructionFallbackStrategy')) {
                $constructionFallbackStrategy = $context->getAttribute('constructionFallbackStrategy');
            }
            switch ($constructionFallbackStrategy) {
                case self::ON_MISSING_NULL:
                    return null;
                case self::ON_MISSING_EXCEPTION:
                    throw new ObjectConstructionException(\sprintf('Entity %s can not be found', $metadata->name));
                case self::ON_MISSING_FALLBACK:
                case null:
                    return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
                default:
                    throw new InvalidArgumentException('The context constructionFallbackStrategy');
            }
        }

        return $object;
    }

    private function loadObject(string $class, $data, DeserializationContext $context, array $path): ?object
    {
        $objectManager = $this->managerRegistry->getManagerForClass($class);
        $classMetadataFactory = $objectManager->getMetadataFactory();

        if ($classMetadataFactory->isTransient($class)) {
            return null;
        }

        $classMetadata = $objectManager->getClassMetadata($class);
        $serializationMetadata = $this->metadataFactory->getMetadataForClass($class);

        $doctrineFindByFields = null;
        $findByIdentifier = false; // This will allow an optimization on the find method
        if ($context->hasAttribute('doctrineFindByFieldsMap')) {
            $doctrineFindByFieldsMap = $context->getAttribute('doctrineFindByFieldsMap');
            if (isset($doctrineFindByFieldsMap[0])) {
                // This is to create a alias since the path will not be 0 but rather ""
                $doctrineFindByFieldsMap[''] = $doctrineFindByFieldsMap[0];
            }
            $pathAsString = implode('.', $path);
            if (isset($doctrineFindByFieldsMap[$pathAsString])) {
                $doctrineFindByFields = $doctrineFindByFieldsMap[$pathAsString];
            }
        }

        if (null === $doctrineFindByFields) {
            $doctrineFindByFields = $classMetadata->getIdentifierFieldNames();
            $findByIdentifier = true;
        }

        if ($findByIdentifier && !\is_array($data) && 1 == \count($doctrineFindByFields)) {
            $object = $objectManager->find($class, $data);
        } else {
            if (!\is_array($data)) {
                return null;
            }
            $criteria = [];
            foreach ($doctrineFindByFields as $name) {
                if ($serializationMetadata && isset($serializationMetadata->propertyMetadata[$name])) {
                    $dataName = $serializationMetadata->propertyMetadata[$name]->serializedName ?? $name;
                } else {
                    $dataName = $name;
                }

                if (!isset($data[$dataName])) {
                    return null;
                }

                if ($classMetadata->hasAssociation($name)) {
                    $data[$dataName] = $this->loadObject(
                        $classMetadata->getAssociationTargetClass($name),
                        $data[$dataName],
                        $context,
                        $path + [$name]
                    );
                }

                $criteria[$name] = $data[$dataName];
            }

            if (empty($criteria)) {
                return null;
            }

            if ($findByIdentifier) {
                $object = $objectManager->find($class, $criteria);
            } else {
                $object = $objectManager->getRepository($class)->findOneBy($criteria);
            }
        }

        if ($object) {
            $objectManager->initializeObject($object);
        }

        return $object;
    }
}
