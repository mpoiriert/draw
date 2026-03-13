<?php

namespace Draw\Component\DataSynchronizer\Serializer\Metadata\Driver;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Metadata\Driver\DriverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DoctrineTypeDriver extends \JMS\Serializer\Metadata\Driver\DoctrineTypeDriver
{
    public function __construct(
        #[Autowire(service: YamlDriver::class)]
        DriverInterface $delegate,
        ManagerRegistry $registry,
    ) {
        parent::__construct($delegate, $registry);
        $this->fieldMapping['json'] = 'array';
    }

    public function getType(ClassMetadata $doctrineMetadata, $propertyName): ?string
    {
        $doctrineType = $doctrineMetadata->getTypeOfField($propertyName);
        if ($doctrineMetadata->hasField($propertyName)) {
            if ($enumType = $doctrineMetadata->getFieldMapping($propertyName)['enumType'] ?? null) {
                return "Enum<{$enumType}>";
            }
            if ($fieldType = $this->normalizeFieldType($doctrineType)) {
                return $fieldType;
            }
        }

        if ($doctrineMetadata->hasAssociation($propertyName)) {
            $targetEntity = $doctrineMetadata->getAssociationTargetClass($propertyName);
            if (null === $this->tryLoadingDoctrineMetadata($targetEntity)) {
                return null;
            }

            if (!$doctrineMetadata->isSingleValuedAssociation($propertyName)) {
                return "ExtractionReferenceCollection<{$propertyName},{$targetEntity}>";
            }

            return "ExtractionReference<{$propertyName},{$targetEntity}>";
        }

        return $doctrineType;
    }
}
