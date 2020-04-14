<?php namespace Draw\Bundle\OpenApiBundle\JmsSerializer\Construction;

use Doctrine\Common\Persistence\ManagerRegistry;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\Exception\InvalidArgumentException;
use JMS\Serializer\Exception\ObjectConstructionException;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\DeserializationContext;
use Metadata\MetadataFactoryInterface;

class DoctrineObjectConstructor implements ObjectConstructorInterface
{
    public const ON_MISSING_NULL = 'null';
    public const ON_MISSING_EXCEPTION = 'exception';
    public const ON_MISSING_FALLBACK = 'fallback';

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var ObjectConstructorInterface
     */
    private $fallbackConstructor;

    /**
     * @param ManagerRegistry $managerRegistry Manager registry
     * @param ObjectConstructorInterface $fallbackConstructor Fallback object constructor
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ObjectConstructorInterface $fallbackConstructor,
        MetadataFactoryInterface $metadataFactory
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->metadataFactory = $metadataFactory;
        $this->fallbackConstructor = $fallbackConstructor;
    }

    /**
     * {@inheritdoc}
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        // Locate possible ObjectManager
        $objectManager = $this->managerRegistry->getManagerForClass($metadata->name);

        if (!$objectManager) {
            // No ObjectManager found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        //If the object is not found we relay on the fallback constructor
        if (is_null($object = $this->loadObject($metadata->name, $data, $context, $context->getCurrentPath()))) {
            $constructionFallbackStrategy = null;
            if($context->hasAttribute('constructionFallbackStrategy')) {
                $constructionFallbackStrategy = $context->getAttribute('constructionFallbackStrategy');
            }
            switch ($constructionFallbackStrategy) {
                case self::ON_MISSING_NULL:
                    return null;
                case self::ON_MISSING_EXCEPTION:
                    throw new ObjectConstructionException(sprintf('Entity %s can not be found', $metadata->name));
                case self::ON_MISSING_FALLBACK:
                case null:
                    return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
                default:
                    throw new InvalidArgumentException('The context constructionFallbackStrategy');
            }
        }

        return $object;
    }

    private function loadObject($class, $data, DeserializationContext $context, array $path)
    {
        $objectManager = $this->managerRegistry->getManagerForClass($class);
        $classMetadataFactory = $objectManager->getMetadataFactory();

        if ($classMetadataFactory->isTransient($class)) {
            return null;
        }

        $classMetadata = $objectManager->getClassMetadata($class);
        $serializationMetadata = $this->metadataFactory->getMetadataForClass($class);

        $doctrineFindByFields = null;
        $findByIdentifier = false; //This will allow an optimization on the find method
        if($context->hasAttribute('doctrineFindByFieldsMap')) {
            $doctrineFindByFieldsMap = $context->getAttribute('doctrineFindByFieldsMap');
            if(isset($doctrineFindByFieldsMap[0])) {
                //This is to create a alias since the path will not be 0 but rather ""
                $doctrineFindByFieldsMap[""] = $doctrineFindByFieldsMap[0];
            }
            $pathAsString = implode('.', $path);
            if(isset($doctrineFindByFieldsMap[$pathAsString])) {
                $doctrineFindByFields = $doctrineFindByFieldsMap[$pathAsString];
            }
        }

        if(is_null($doctrineFindByFields)) {
            $doctrineFindByFields = $classMetadata->getIdentifierFieldNames();
            $findByIdentifier = true;
        }

        if($findByIdentifier && !is_array($data) && count($doctrineFindByFields) == 1) {
            $object = $objectManager->find($class, $data);
        } else {
            if (!is_array($data)) {
                return null;
            }
            $criteria = [];
            foreach ($doctrineFindByFields as $name) {
                if ($serializationMetadata && isset($serializationMetadata->propertyMetadata[$name])) {
                    $dataName = $serializationMetadata->propertyMetadata[$name]->serializedName;
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

            if($findByIdentifier) {
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